<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LoanResource;
use App\Jobs\GenerateRepaymentSchedules;
use App\Jobs\MailLoanRequestCreated;
use App\Jobs\MailRejectedLoanRequest;
use App\Jobs\ProcessPayment;
use App\Models\Payment;
use App\Models\WeeklyLoan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


/**
 * Controller to handle weekly loan requests.
 */
class WeeklyLoanController extends Controller
{
    public function __construct()
    {
        $this->middleware('idempotent')->only('store');
    }

    /**
     * Show list of data with 10 data per page.
     *
     * @return LoanResource
     */
    public function index()
    {
        $user = auth()->user();
        $loans = $user->is_admin ? 
                WeeklyLoan::latest()->paginate(10)
                :
                WeeklyLoan::where('created_by', auth()->user()->id)->latest()->paginate(10);
        
        return new LoanResource(true, 'OK', $loans);
    }

    /**
     * Show detail loan request by hashids.
     *
     * @param string $hid
     * @return LoanResource
     */
    public function show($hid)
    {
        $user = auth()->user();
        
        try {
            $loan = WeeklyLoan::findOrFailByHashid($hid);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $th) {
            return response('',404);
        }

        if (!$user->is_admin && $loan->created_by != $user->id) {
            return response('',404);
        }

        return new LoanResource(true, 'OK', $loan);
    }

    /**
     * Create new weekly loan request.
     *
     * @param Request $request
     * @return LoanResource
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|regex:/^\d+(\.\d{1,2})?$/|numeric|min:100|max:1000000',
            'loan_term' => 'required|numeric|min:2|max:52'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $request->all();
        $loan = new WeeklyLoan($data);
        $loan->remaining = $loan->amount;
        $loan->status = WeeklyLoan::STATUS_PENDING;
        $loan->save();

        dispatch(new MailLoanRequestCreated($loan));
        sentry_log("[WeeklyLoanController][Store] User ID {$loan->created_by} create new loan request hashid {$loan->hashid}.");

        return new LoanResource(true, 'OK', $loan);
    }

    /**
     * Approve loan request made by user.
     * NOTE : this function only accessed by admin role user.
     *
     * @param string $hid hashids
     * @return \Illuminate\Http\Response
     */
    public function approve($hid)
    {
        try {
            $loan = WeeklyLoan::findOrFailByHashid($hid);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $th) {
            return response('',404);
        }
        $errorMessage = $loan->changeStatus(WeeklyLoan::STATUS_APPROVED);

        if ($errorMessage) {
            return response()->json([
                'status' => false,
                'message' => $errorMessage
            ]);
        } 

        dispatch(new GenerateRepaymentSchedules($loan));
        sentry_log("[WeeklyLoanController][Approve] Admin ID {$loan->updated_by} approve loan request hashid {$loan->hashid}.");

        return response()->json([
            'status' => true,
            'message' => 'OK'
        ]);
    }

    /**
     * Reject loan request made by user.
     * NOTE : this function only accessed by admin role user.
     *
     * @param string $hid hashids
     * @return \Illuminate\Http\Response
     */
    public function reject($hid)
    {
        try {
            $loan = WeeklyLoan::findOrFailByHashid($hid);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $th) {
            return response('',404);
        }
        $errorMessage = $loan->changeStatus(WeeklyLoan::STATUS_REJECTED);

        if ($errorMessage) {
            return response()->json([
                'status' => false,
                'message' => $errorMessage
            ]);
        }

        dispatch(new MailRejectedLoanRequest($loan));
        sentry_log("[WeeklyLoanController][Reject] Admin ID {$loan->updated_by} reject loan request hashid {$loan->hashid}.");
        
        return response()->json([
            'status' => true,
            'message' => 'OK'
        ]);
    }

    /**
     * Customer pay the repayment schedule.
     *
     * @param Request $request
     * @param string $hid
     * @return \Illuminate\Http\Response
     */
    public function payment(Request $request, $hid){
        $loan = WeeklyLoan::findOrFailByHashid($hid);
        if (!$loan || $loan->created_by != auth()->user()->id) {
            return response()->json('', 404);
        }

        if ($loan->status == WeeklyLoan::getStatusText(WeeklyLoan::STATUS_PENDING)) {
            return response()->json(['message' => 'Loan status is still PENDING.'], 422);
        }
        
        $validator = Validator::make($request->all(), [
            'amount' => 'required|regex:/^\d+(\.\d{1,2})?$/|numeric|min:1|max:'.$loan->amount,
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $payment = Payment::create([
            'weekly_loan_id' => $loan->id,
            'amount' => $request->all()['amount']
        ]);

        dispatch(new ProcessPayment($payment));

        return new LoanResource(true, "OK", $payment);
    }
}
