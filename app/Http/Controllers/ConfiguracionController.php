<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidarConfiguracionRequest;
use App\Models\Configuracion;

class ConfiguracionController extends Controller
{
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Configuracion $configuracion)
    {
        return inertia('configuracion/edit', [
            'configuracion' => $configuracion,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ValidarConfiguracionRequest $request, Configuracion $configuracion)
    {
        $validated = $request->validated();

        $configuracion->update($validated);

        return redirect()->route('configuracion.edit', $configuracion->id)
        ->with('success', 'El registro se actualizó con éxito');
    }
}
