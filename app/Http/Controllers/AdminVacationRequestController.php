<?php

namespace App\Http\Controllers;

use App\Mail\VacationRequestStatusMail;
use App\Models\Vacation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AdminVacationRequestController extends Controller
{
    private function redirectToIndex()
    {
        return redirect()->route('admin.worktime.requests.index');
    }

    public function approve(Vacation $vacation)
    {
        if ($vacation->status !== 'pending') {
            return $this->redirectToIndex()->with('error', 'Dieser Urlaubsantrag wurde bereits bearbeitet.');
        }

        $vacation->update([
            'status' => 'approved',
        ]);

        $vacation->loadMissing('user');
        if ($vacation->user?->email) {
            Mail::to($vacation->user->email)->send(new VacationRequestStatusMail($vacation));
        }

        return $this->redirectToIndex()->with('success', 'Urlaubsantrag wurde genehmigt.');
    }

    public function reject(Request $request, Vacation $vacation)
    {
        if ($vacation->status !== 'pending') {
            return $this->redirectToIndex()->with('error', 'Dieser Urlaubsantrag wurde bereits bearbeitet.');
        }

        $vacation->update([
            'status' => 'rejected',
        ]);

        $vacation->loadMissing('user');
        if ($vacation->user?->email) {
            Mail::to($vacation->user->email)->send(new VacationRequestStatusMail($vacation));
        }

        return $this->redirectToIndex()->with('success', 'Urlaubsantrag wurde abgelehnt.');
    }
}
