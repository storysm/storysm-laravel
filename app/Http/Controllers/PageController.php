<?php

namespace App\Http\Controllers;

use App\Enums\Page\Status;
use App\Models\Page;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class PageController extends Controller
{
    /**
     * Display the terms of service page.
     */
    public function terms(): View
    {
        $pageId = config('page.terms');
        if (! $pageId) {
            abort(404);
        }
        $record = Page::whereStatus(Status::Publish)
            ->findOrFail($pageId);

        return view('terms-of-service', ['record' => $record]);
    }

    /**
     * Display the privacy policy page.
     */
    public function policy(): View
    {
        $pageId = config('page.privacy');
        if (! $pageId) {
            abort(404);
        }
        $record = Page::whereStatus(Status::Publish)
            ->findOrFail($pageId);

        return view('privacy-policy', ['record' => $record]);
    }

    /**
     * Display the age rating guidelines page.
     */
    public function ageRatings(): View
    {
        $pageId = config('page.age_ratings');
        if (! $pageId) {
            abort(404);
        }
        $record = Page::whereStatus(Status::Publish)
            ->findOrFail($pageId);

        return view('age-ratings', ['record' => $record]);
    }

    /**
     * Display the cookie policy page.
     */
    public function cookie(): View
    {
        $pageId = config('page.cookie');
        if (! $pageId) {
            abort(404);
        }
        $record = Page::whereStatus(Status::Publish)
            ->findOrFail($pageId);

        return view('cookie', ['record' => $record]);
    }

    /**
     * Handle the fallback route.
     */
    public function fallback(): Response
    {
        return response()->view('errors.404', [], 404);
    }
}
