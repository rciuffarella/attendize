<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    /**
     * Home pubblica generale: mostra tutti gli eventi pubblici (di tutti gli organizzatori).
     *
     * @param  Request  $request
     * @return \Illuminate\Contracts\View\View
     */
    public function showIndex(Request $request)
    {
        // Eventi futuri, in ordine cronologico
        $upcoming_events = Event::where('is_live', 1)
            ->where('end_date', '>=', now())
            ->orderBy('start_date', 'asc')
            ->get();

        // Eventi passati (ultimi 20)
        $past_events = Event::where('is_live', 1)
            ->where('end_date', '<', now())
            ->orderBy('end_date', 'desc')
            ->limit(20)
            ->get();

        return view('Public.GeneralEvents.index', [
            'upcoming_events' => $upcoming_events,
            'past_events'     => $past_events,
        ]);
    }
}
