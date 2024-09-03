<?php
namespace App\Http\Controllers\Ekpay;

use App\Models\Payment;
use App\Models\Sonod;
use App\Models\HoldingBokeya;
use App\Models\Tender;
use App\Models\TenderList;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class EkpayPaymentController extends Controller
{
    public function ipn(Request $request)
    {
        $data = $request->all();
        Log::info('IPN Data: ' . json_encode($data));

        $sonod = Sonod::find($data['cust_info']['cust_id']);
        $trnx_id = $data['trnx_info']['mer_trnx_id'];
        $payment = Payment::where('trxid', $trnx_id)->first();

        $insertData = [];

        if ($data['msg_code'] == '1020') {
            $insertData = [
                'status' => 'Paid',
                'method' => $data['pi_det_info']['pi_name'],
            ];

            if ($payment->sonod_type == 'holdingtax') {
                $holdingBokeya = HoldingBokeya::find($payment->sonodId);
                $holdingBokeya->update([
                    'status' => 'Paid',
                    'payYear' => date('Y'),
                    'payOB' => COB(1)
                ]);
                return response()->json([
                    'success' => true,
                    'message' => 'Holding Bokeya payment has been successfully updated.',
                ], 200);
            } elseif ($payment->sonod_type == 'Tenders_form') {
                $tenderFormBuy = Tender::find($payment->sonodId);
                $tenderFormBuy->update(['payment_status' => 'Paid']);

                $tenderList = TenderList::find($tenderFormBuy->tender_id);
                $unionName = $tenderList->union_name;
                $description = "Your Tender has been successfully submitted.";
                SmsNocSmsSend($description, $tenderFormBuy->mobile, $unionName);

                return response()->json([
                    'success' => true,
                    'message' => 'Tender form payment has been successfully updated.',
                ], 200);
            } else {
                $sonod->update([
                    'khat' => 'সনদ ফি',
                    'status' => 'Pending',
                    'payment_status' => 'Paid'
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Sonod payment has been successfully updated.',
                ], 200);
            }
        } else {
            $sonod->update([
                'khat' => 'সনদ ফি',
                'status' => 'Failed',
                'payment_status' => 'Failed'
            ]);
            $insertData = ['status' => 'Failed'];

            return response()->json([
                'success' => false,
                'message' => 'Payment failed. Please try again.',
            ], 400);
        }

        $insertData['ipnResponse'] = json_encode($data);
        return response()->json([
            'success' => true,
            'message' => 'IPN response recorded.',
        ], 200);
    }

    public function ReCallIpn(Request $request)
    {
        $trnx_id = $request->trnx_id;
        $trans_date = date("Y-m-d", strtotime($request->trans_date));

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://pg.ekpay.gov.bd/ekpaypg/v1/get-status',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode([
                'trnx_id' => $trnx_id,
                'trans_date' => $trans_date
            ]),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);
        $data = json_decode($response);

        Log::info('Recalled IPN Data: ' . json_encode($data));

        $sonod = Sonod::find($data->cust_info->cust_id);
        $trnx_id = $data->trnx_info->mer_trnx_id;
        $payment = Payment::where('trxid', $trnx_id)->first();

        $insertData = [];
        if ($data->msg_code == '1020') {
            $insertData = [
                'status' => 'Paid',
                'method' => $data->pi_det_info->pi_name,
            ];

            if ($payment->sonod_type == 'holdingtax') {
                $holdingBokeya = HoldingBokeya::find($payment->sonodId);
                $holdingBokeya->update([
                    'status' => 'Paid',
                    'payYear' => date('Y')
                ]);
                return response()->json([
                    'success' => true,
                    'message' => 'Recalled Holding Bokeya payment has been successfully updated.',
                ], 200);
            } else {
                $sonod->update([
                    'khat' => 'সনদ ফি',
                    'status' => 'Pending',
                    'payment_status' => 'Paid'
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Recalled Sonod payment has been successfully updated.',
                ], 200);
            }
        } else {
            $sonod->update([
                'khat' => 'সনদ ফি',
                'status' => 'Failed',
                'payment_status' => 'Failed'
            ]);
            $insertData = ['status' => 'Failed'];

            return response()->json([
                'success' => false,
                'message' => 'Recalled payment failed. Please try again later.',
            ], 400);
        }

        $insertData['ipnResponse'] = json_encode($data);
        return response()->json([
            'success' => true,
            'message' => 'Recalled IPN response recorded.',
        ], 200);
    }

    public function AkpayPaymentCheck(Request $request)
    {
        $trnx_id = $request->trnx_id;
        $trans_date = date("Y-m-d", strtotime($request->trans_date));

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://pg.ekpay.gov.bd/ekpaypg/v1/get-status',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode([
                'trnx_id' => $trnx_id,
                'trans_date' => $trans_date
            ]),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        $myServerPayment = Payment::where(['trxId' => $trnx_id])->first();
        $akpayResponse = json_decode($response);

        return response()->json([
            'success' => true,
            'message' => 'Payment status checked successfully.',
            'data' => [
                'myserver' => $myServerPayment,
                'akpay' => $akpayResponse,
            ],
        ], 200);
    }

    public function handlePaymentSuccess(Request $request)
    {
        $transId = $request->transId;
        $payment = Payment::where('trxId', $transId)->first();

        $redirect = "/payment/success/confirm?transId=$transId";

        return response()->json([
            'success' => true,
            'message' => 'Please wait 10 seconds. This page will auto redirect you.',
            'redirect_url' => $redirect,
        ], 200);
    }

    public function sonodpaymentSuccess(Request $request)
    {
        $transId = $request->transId;
        $payment = Payment::where(['trxId' => $transId])->first();
        $id = $payment->sonodId;

        $sonod = Sonod::find($id);

        if ($payment->status == 'Paid') {
            $invoiceUrl = url("/invoice/c/$id");
            $description = "Congratulations! Your application {$sonod->sonod_Id} has been paid. Wait for approval.";
            // Uncomment to send SMS
            // smsSend($description, $sonod->applicant_mobile);

            return response()->json([
                'success' => true,
                'message' => 'Payment successful! Your application is now under review.',
                'data' => [
                    'payment' => $payment,
                    'sonod' => $sonod,
                    'invoice_url' => $invoiceUrl
                ]
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Payment failed. Please try again.',
            ], 400);
        }
    }
}
