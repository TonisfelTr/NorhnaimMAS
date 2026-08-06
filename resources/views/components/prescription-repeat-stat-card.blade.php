<article {{ $attributes->merge(['class' => 'rx-repeat-stat-card']) }}>
    <div class="rx-repeat-stat-card__head">
        <div class="rx-repeat-stat-card__title">
            Повторно оформлено сегодня
        </div>

        <div class="rx-repeat-stat-card__icon" aria-hidden="true">
            <i class="bi bi-arrow-repeat"></i>
        </div>
    </div>

    <div class="rx-repeat-stat-card__value">
        {{ $count }}
    </div>

    <div class="rx-repeat-stat-card__description">
        Новые рецепты текущего врача, созданные сегодня
        на основании ранее оформленного рецепта
    </div>
</article>

@once
    <style>
        .rx-repeat-stat-card {
            position: relative;
            min-height: 142px;
            padding: 20px;
            border: 1px solid #dce9ed;
            border-top: 4px solid #16a34a;
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 8px 24px rgba(15, 23, 42, .04);
        }

        .rx-repeat-stat-card__head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }

        .rx-repeat-stat-card__title {
            color: #475569;
            font-size: 13px;
            font-weight: 800;
            line-height: 1.35;
        }

        .rx-repeat-stat-card__icon {
            display: grid;
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            place-items: center;
            border-radius: 11px;
            background: #eefbf3;
            color: #15803d;
            font-size: 16px;
        }

        .rx-repeat-stat-card__value {
            margin-top: 8px;
            color: #0f172a;
            font-size: 30px;
            font-weight: 900;
            line-height: 1;
        }

        .rx-repeat-stat-card__description {
            margin-top: 10px;
            color: #7b8794;
            font-size: 12px;
            line-height: 1.45;
        }
    </style>
@endonce
