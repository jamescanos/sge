<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBrandingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // el permiso editar-branding lo exige el middleware del controlador
    }

    public function rules(): array
    {
        return [
            'trade_name' => ['required', 'string', 'max:80'],
            'legal_name' => ['required', 'string', 'max:150'],
            'nit' => ['required', 'string', 'max:20', 'regex:/^[0-9][0-9.\-]*$/'],
            'slogan' => ['nullable', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:150'],
            'city' => ['nullable', 'string', 'max:80'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:120'],
            'website' => ['nullable', 'url', 'max:150'],
            'primary_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            // Solo imágenes de mapa de bits (no SVG: un SVG puede contener código JavaScript)
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:1024'],
            'remove_logo' => ['nullable', 'boolean'],
        ];
    }

    // Mensajes en español (la aplicación usa el idioma por defecto de Laravel: inglés)
    public function messages(): array
    {
        return [
            'trade_name.required' => 'El nombre comercial es obligatorio.',
            'trade_name.max' => 'El nombre comercial no puede superar los 80 caracteres.',
            'legal_name.required' => 'La razón social es obligatoria.',
            'legal_name.max' => 'La razón social no puede superar los 150 caracteres.',
            'nit.required' => 'El NIT es obligatorio.',
            'nit.max' => 'El NIT no puede superar los 20 caracteres.',
            'nit.regex' => 'El NIT solo puede tener números, puntos y guion (ejemplo: 901.234.567-8).',
            'slogan.max' => 'El eslogan no puede superar los 120 caracteres.',
            'email.email' => 'El correo electrónico no es válido.',
            'website.url' => 'El sitio web debe ser una URL completa (ejemplo: https://www.empresa.com).',
            'primary_color.required' => 'El color de marca es obligatorio.',
            'primary_color.regex' => 'El color de marca debe tener el formato #RRGGBB (ejemplo: #0f766e).',
            'logo.image' => 'El logo debe ser una imagen.',
            'logo.mimes' => 'El logo debe ser PNG, JPG o WEBP.',
            'logo.max' => 'El logo no puede pesar más de 1 MB.',
        ];
    }
}
