<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Menu – De Gouden Draak</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; color: #333; }
        h1 { color: #C0392B; text-align: center; font-size: 24px; }
        h2 { color: #C0392B; font-size: 16px; border-bottom: 1px solid #C0392B; padding-bottom: 4px; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; font-size: 11px; }
        td { padding: 3px 6px; vertical-align: top; }
        .nummer { width: 40px; font-weight: bold; }
        .naam { width: 55%; }
        .prijs { text-align: right; white-space: nowrap; }
        .prijs-promo { text-decoration: line-through; color: #999; }
        .promo-prijs { color: #C0392B; font-weight: bold; }
        .aanbiedingen-pagina { page-break-before: always; }
        .logo { text-align: center; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="logo">
        <h1>De Gouden Draak</h1>
        <p style="text-align:center;font-size:11px;color:#666;">{{ now()->format('d-m-Y') }}</p>
    </div>

    @foreach($categories as $category)
        <h2>{{ $category->name }}</h2>
        <table>
            @foreach($category->activeProducts as $product)
                @php
                    $promo = $product->promotions->first();
                    $currentPrice = $product->current_price;
                @endphp
                <tr>
                    <td class="nummer">{{ $product->menu_number }}</td>
                    <td class="naam">{{ $product->name }}
                        @if($product->description)
                            <br><span style="font-size:9px;color:#666;">{{ $product->description }}</span>
                        @endif
                    </td>
                    <td class="prijs">
                        @if($promo)
                            <span class="prijs-promo">€{{ number_format($product->price, 2, ',', '.') }}</span><br>
                            <span class="promo-prijs">€{{ number_format($currentPrice, 2, ',', '.') }}</span>
                        @else
                            €{{ number_format($currentPrice, 2, ',', '.') }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>
    @endforeach

    @if($activePromotions->isNotEmpty())
        <div class="aanbiedingen-pagina">
            <h1>Aanbiedingen</h1>
            @foreach($activePromotions as $promo)
                <h2>{{ $promo->name }}</h2>
                @if($promo->description)
                    <p style="font-size:11px;">{{ $promo->description }}</p>
                @endif
                <table>
                    @foreach($promo->products as $product)
                        <tr>
                            <td class="nummer">{{ $product->menu_number }}</td>
                            <td class="naam">{{ $product->name }}</td>
                            <td class="prijs">
                                <span class="prijs-promo">€{{ number_format($product->price, 2, ',', '.') }}</span>
                                <span class="promo-prijs">€{{ number_format($product->current_price, 2, ',', '.') }}</span>
                            </td>
                        </tr>
                    @endforeach
                </table>
                <p style="font-size:9px;color:#666;">
                    Geldig van {{ $promo->valid_from->format('d-m-Y') }} t/m {{ $promo->valid_until->format('d-m-Y') }}
                </p>
            @endforeach
        </div>
    @endif
</body>
</html>
