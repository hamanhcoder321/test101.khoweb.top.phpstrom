<?php
namespace App\Modules\AI\Controllers;
use App\CRMDV\Models\Lead;
use App\Services\SalesAIService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
class LeadAIController extends Controller
{
    public function ask(Request $request)
    {
        try {
            $lead = Lead::findOrFail($request->lead_id);

            $leadData = [
                'name' => $lead->name ?? '',
                'phone' => $lead->tel ?? '',
                'email' => $lead->email ?? '',
                'location' => $lead->tinh ?? '',
                'source' => $lead->source ?? '',
                'service' => $lead->service ?? '',
                'project' => $lead->project ?? '',
                'topic' => $lead->topic ?? '',
                'need' => $lead->need ?? '',
                'status' => $lead->status ?? '',
                'rate' => $lead->rate ?? '',
                'last_contact' => $lead->contacted_log_last ?? '',
                'product' => $lead->product ?? '',
                'discount' => $lead->discount ?? '',
                'reason_refusal' => $lead->reason_refusal ?? '',
                'received_date' => $lead->received_date ?? '',
                'tags' => $lead->tags ?? '',
                'company' => $lead->company ?? '',
            ];

            $ai = new SalesAIService();
            $answer = $ai->askLead($leadData, $request->question);

            return response()->json(['answer' => $answer]);

        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
