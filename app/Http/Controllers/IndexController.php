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
        $query = Event::where('is_live', 1);

        // Filtro per ricerca testuale
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%')
                  ->orWhere('venue_name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('location', 'like', '%' . $searchTerm . '%')
                  ->orWhere('category', 'like', '%' . $searchTerm . '%');
            });
        }
        
        // Filtro per categoria specifica
        if ($request->has('category') && !empty($request->category)) {
            $query->where('category', $request->category);
        }

        // Eventi futuri, in ordine cronologico
        $upcoming_events = (clone $query)
            ->where('end_date', '>=', now())
            ->orderBy('start_date', 'asc')
            ->get();

        // Eventi passati (ultimi 20) - solo se non c'è ricerca o filtro categoria attivo
        $past_events = collect();
        if ((!$request->has('search') || empty($request->search)) && (!$request->has('category') || empty($request->category))) {
            $past_events = Event::where('is_live', 1)
                ->where('end_date', '<', now())
                ->orderBy('end_date', 'desc')
                ->limit(20)
                ->get();
        }

        // Top 5 eventi per banner (eventi futuri più prossimi) - solo se non c'è ricerca o filtro categoria
        $featured_events = collect();
        if ((!$request->has('search') || empty($request->search)) && (!$request->has('category') || empty($request->category))) {
            $featured_events = Event::where('is_live', 1)
                ->where('end_date', '>=', now())
                ->orderBy('start_date', 'asc')
                ->limit(5)
                ->get();
        }

        // Categorie predefinite (box colorati)
        $categories = [
            [
                'name' => 'Turismo',
                'icon' => 'ico-map',
                'color' => '#3B82F6',
                'description' => 'Attrazioni e tour'
            ],
            [
                'name' => 'Cultura',
                'icon' => 'ico-picture',
                'color' => '#8B5CF6',
                'description' => 'Mostre e musei'
            ],
            [
                'name' => 'Spettacoli',
                'icon' => 'ico-music',
                'color' => '#EC4899',
                'description' => 'Teatro e concerti'
            ],
            [
                'name' => 'Eventi musicali',
                'icon' => 'ico-headphones',
                'color' => '#F59E0B',
                'description' => 'Concerti e live'
            ],
            [
                'name' => 'Cibo & Drink',
                'icon' => 'ico-food',
                'color' => '#10B981',
                'description' => 'Degustazioni e ristoranti'
            ],
            [
                'name' => 'Sport',
                'icon' => 'ico-trophy',
                'color' => '#EF4444',
                'description' => 'Eventi sportivi'
            ],
        ];

        return view('Public.GeneralEvents.index', [
            'upcoming_events' => $upcoming_events,
            'past_events'     => $past_events,
            'featured_events' => $featured_events,
            'categories'     => $categories,
            'search_term'    => $request->search ?? '',
            'category_filter' => $request->category ?? '',
        ]);
    }
}
