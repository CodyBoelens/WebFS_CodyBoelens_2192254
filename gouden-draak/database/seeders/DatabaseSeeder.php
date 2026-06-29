<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Table;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin gebruiker
        User::create([
            'name'     => 'Administrator',
            'email'    => 'admin@goudendraak.nl',
            'password' => Hash::make('goudendraak2025'),
            'role'     => 'admin',
        ]);

        User::create([
            'name'     => 'Kassamedewerker',
            'email'    => 'kassa@goudendraak.nl',
            'password' => Hash::make('kassa2025'),
            'role'     => 'kassa',
        ]);

        // Tafels
        foreach (range(1, 15) as $nr) {
            Table::create(['number' => (string) $nr, 'seats' => ($nr <= 10 ? 4 : 8)]);
        }

        // Categorieën
        $categorieën = [
            ['name' => 'Voorgerechten',      'sort_order' => 1],
            ['name' => 'Soepen',             'sort_order' => 2],
            ['name' => 'Rijst & Noodles',    'sort_order' => 3],
            ['name' => 'Vlees',              'sort_order' => 4],
            ['name' => 'Vis & Zeevruchten',  'sort_order' => 5],
            ['name' => 'Vegetarisch',        'sort_order' => 6],
            ['name' => 'Desserts',           'sort_order' => 7],
            ['name' => 'Dranken',            'sort_order' => 8],
        ];

        foreach ($categorieën as $cat) {
            Category::create([
                'name'       => $cat['name'],
                'slug'       => Str::slug($cat['name']),
                'sort_order' => $cat['sort_order'],
                'active'     => true,
            ]);
        }

        // Voorbeeldproducten (gemigreerd vanuit oude database structuur)
        $products = [
            [1, '1',   'Loempia (2 stuks)',            'Krokante loempia gevuld met groenten',   5.50],
            [1, '2',   'Satéstokjes (4 stuks)',         'Kip satéstokjes met pindasaus',          8.00],
            [1, '3',   'Kroepoek',                      null,                                     3.50],
            [2, '4',   'Tom Kha Gai soep',              'Kokos kippensoep',                       7.50],
            [2, '5',   'Chinese kippensoep',            null,                                     6.50],
            [3, '10',  'Nasi goreng',                   'Gebakken rijst met ei en groenten',      12.50],
            [3, '10a', 'Nasi goreng speciaal',          'Met kip, garnalen en groenten',          15.50],
            [3, '11',  'Bami goreng',                   'Gebakken noedels',                       12.50],
            [3, '11a', 'Bami goreng speciaal',          'Met kip en garnalen',                    15.50],
            [3, '12',  'Mihoen goreng',                 'Rijstnoedels gebakken',                  12.50],
            [4, '20',  'Babi pangang',                  'Knapperig varkensvlees in zoetzure saus', 16.50],
            [4, '21',  'Eend in hoisinsaus',            null,                                     18.50],
            [4, '22',  'Kip in zwarte bonensaus',       null,                                     15.50],
            [4, '23',  'Biefstuk met oestersaus',       null,                                     19.50],
            [5, '30',  'Garnalen met knoflook',         null,                                     17.50],
            [5, '31',  'Kreeft op Kantonese wijze',     null,                                     28.50],
            [5, '32',  'Inktvis met groenten',          null,                                     16.50],
            [6, '40',  'Tofoe in zoetzure saus',        null,                                     13.50],
            [6, '41',  'Roergebakken groenten',         null,                                     12.50],
            [7, '50',  'Mangopudding',                  null,                                      5.00],
            [7, '51',  'Gefrituurd ijs',                null,                                      6.50],
            [8, '60',  'Jasmine thee (pot)',            null,                                      3.50],
            [8, '61',  'Chinese bier',                  null,                                      4.00],
            [8, '62',  'Frisdrank',                     null,                                      3.00],
        ];

        foreach ($products as $i => [$catOffset, $nr, $name, $desc, $price]) {
            Product::create([
                'category_id' => $catOffset,
                'menu_number' => $nr,
                'name'        => $name,
                'description' => $desc,
                'price'       => $price,
                'active'      => true,
                'sort_order'  => $i,
            ]);
        }

        // Standaard opmerkingen seeden (US-10) zodat de suggestielijst meteen gevuld is
        $demoOrder = Order::create([
            'source' => 'kassa', 'status' => 'betaald', 'round' => 1, 'total' => 0,
        ]);
        $standaardOpmerkingen = [
            'Geen ui', 'Extra saus', 'Niet te pittig', 'Extra pittig',
            'Geen koriander', 'Allergie: noten', 'Extra rijst', 'Weinig zout',
        ];
        $eersteProduct = Product::first();
        foreach ($standaardOpmerkingen as $opmerking) {
            OrderItem::create([
                'order_id'   => $demoOrder->id,
                'product_id' => $eersteProduct->id,
                'quantity'   => 1,
                'unit_price' => $eersteProduct->price,
                'note'       => $opmerking,
            ]);
        }
    }
}
