<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreTestSortTableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return is_authed() && $this->user()->can('test_create_with_sort');
    }

    protected function prepareForValidation(): void
    {
        $data = [
            'code' => $this->normalizeCode(
                $this->input('code') ?: $this->input('name')
            ),
        ];

        $cards = $this->input('cards');

        if (is_array($cards)) {
            foreach ($cards as $index => $card) {
                $cards[$index]['code'] = $this->normalizeCode(
                    $card['code'] ?? $card['title'] ?? null
                );
            }

            $data['cards'] = $cards;
        }

        $this->merge($data);
    }

    private function normalizeCode(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return Str::upper(Str::slug($value, '-'));
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['card_sort'])],

            'name' => ['required', 'string', 'max:255'],

            'code' => [
                'required',
                'string',
                'max:100',
                'regex:/^[A-Z0-9-]+$/',
                'unique:tests,code',
            ],

            'status' => ['required', 'string', Rule::in(['официально', 'часто'])],

            'duration' => ['nullable', 'integer', 'min:1', 'max:240'],

            'description' => ['nullable', 'string'],

            'instructions' => ['required', 'string'],

            'sort_direction' => ['required', Rule::in(['left_to_right'])],

            'resource_title' => ['nullable', 'string', 'max:255'],
            'resource_about' => ['nullable', 'string', 'max:1000'],

            'left_label' => ['required', 'string', 'max:255'],
            'right_label' => ['required', 'string', 'max:255'],

            'cards' => ['required', 'array', 'min:2'],

            'cards.*.sort' => ['required', 'integer', 'min:1'],
            'cards.*.title' => ['required', 'string', 'max:255'],

            'cards.*.code' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^[A-Z0-9-]+$/',
            ],

            'cards.*.type' => [
                'required',
                Rule::in(['text', 'color', 'image']),
            ],

            'cards.*.text' => ['nullable', 'string', 'max:255'],

            'cards.*.color' => [
                'nullable',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],

            'cards.*.image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $cards = $this->input('cards', []);

            if (!is_array($cards)) {
                return;
            }

            foreach ($cards as $index => $card) {
                $type = $card['type'] ?? null;

                if ($type === 'text' && empty($card['text'])) {
                    $validator->errors()->add(
                        "cards.$index.text",
                        'Для текстовой карточки нужно заполнить текст.'
                    );
                }

                if ($type === 'color' && empty($card['color'])) {
                    $validator->errors()->add(
                        "cards.$index.color",
                        'Для цветовой карточки нужно указать цвет.'
                    );
                }

                if ($type === 'image' && !$this->hasFile("cards.$index.image")) {
                    $validator->errors()->add(
                        "cards.$index.image",
                        'Для карточки с изображением нужно загрузить картинку.'
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Не указан тип теста.',
            'type.in' => 'Некорректный тип теста.',

            'name.required' => 'Укажите название теста.',

            'code.required' => 'Укажите код теста.',
            'code.unique' => 'Тест с таким кодом уже существует.',
            'code.regex' => 'Код теста может содержать только латиницу, цифры и тире.',

            'status.required' => 'Укажите статус теста.',
            'status.in' => 'Некорректный статус теста.',

            'duration.integer' => 'Длительность должна быть числом.',
            'duration.min' => 'Длительность должна быть не меньше 1 минуты.',
            'duration.max' => 'Длительность не должна превышать 240 минут.',

            'instructions.required' => 'Укажите инструкцию для пациента.',

            'sort_direction.required' => 'Не указано направление сортировки.',
            'sort_direction.in' => 'Некорректное направление сортировки.',

            'left_label.required' => 'Укажите левую границу шкалы.',
            'right_label.required' => 'Укажите правую границу шкалы.',

            'cards.required' => 'Добавьте карточки для сортировки.',
            'cards.array' => 'Карточки должны быть переданы массивом.',
            'cards.min' => 'Нужно добавить минимум 2 карточки.',

            'cards.*.sort.required' => 'У каждой карточки должен быть порядок сортировки.',
            'cards.*.title.required' => 'У каждой карточки должно быть название.',

            'cards.*.code.regex' => 'Код карточки может содержать только латиницу, цифры и тире.',

            'cards.*.type.required' => 'У каждой карточки должен быть выбран тип.',
            'cards.*.type.in' => 'Некорректный тип карточки.',

            'cards.*.color.regex' => 'Цвет карточки должен быть в формате HEX, например #048696.',

            'cards.*.image.image' => 'Файл карточки должен быть изображением.',
            'cards.*.image.mimes' => 'Изображение должно быть в формате jpg, jpeg, png или webp.',
            'cards.*.image.max' => 'Размер изображения карточки не должен превышать 5 МБ.',
        ];
    }
}
