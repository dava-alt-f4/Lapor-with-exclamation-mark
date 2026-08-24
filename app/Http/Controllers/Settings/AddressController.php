<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\AddressUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AddressController extends Controller
{
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/Address', [
            'address' => [
                'country' => $request->user()->country,
                'province' => $request->user()->province,
                'city' => $request->user()->city,
                'district' => $request->user()->district,
            ],
            'status' => $request->session()->get('status'),
        ]);
    }

    public function update(AddressUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated())->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Address updated.')]);

        return to_route('address.edit');
    }
}
