<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['admin', 'cashier', 'secretary'] as $r) {
            Role::findOrCreate($r);
        }
        $mk = fn ($name, $email, $role) => tap(User::firstOrCreate(['email' => $email],
            ['name' => $name, 'password' => Hash::make('password'), 'email_verified_at' => now()]), fn ($u) => $u->syncRoles($role));
        $admin = $mk('DPAM Admin', 'admin@dpam.test', 'admin');
        $mk('Cashier One', 'cashier@dpam.test', 'cashier');
        $mk('Secretary One', 'secretary@dpam.test', 'secretary');

        $cats = collect(['Building Materials', 'Architectural Finishes', 'Hardware & Fixtures', 'Specialized Products'])
            ->mapWithKeys(fn ($n) => [$n => Category::firstOrCreate(['name' => $n])]);

        $products = [
            ['Building Materials', 'Plywood Marine', '4x8 1/2"', 'sheet', 850, 40, 20],
            ['Building Materials', 'Portland Cement', '40kg', 'bag', 265, 200, 50],
            ['Building Materials', 'Deformed Bar 10mm', '6m', 'pc', 215, 5, 30],
            ['Architectural Finishes', 'Latex Paint White', '4L', 'can', 780, 25, 10],
            ['Architectural Finishes', 'Ceramic Tile Gray', '60x60cm', 'box', 1250, 0, 10],
            ['Hardware & Fixtures', 'Common Wire Nail 3"', '1kg', 'kg', 95, 80, 20],
            ['Hardware & Fixtures', 'GI Pipe 1/2"', '3m', 'pc', 320, 12, 15],
            ['Specialized Products', 'Waterproofing Sealant', '1L', 'can', 640, 30, 8],
        ];
        $made = [];
        foreach ($products as [$c,$n,$s,$u,$p,$q,$min]) {
            $pr = Product::firstOrCreate(['category_id' => $cats[$c]->id, 'name' => $n, 'package_size' => $s],
                ['unit_of_measure' => $u, 'unit_price' => $p, 'status' => 'active']);
            Inventory::firstOrCreate(['product_id' => $pr->id], ['quantity' => $q, 'minimum_stock' => $min]);
            $made[] = $pr;
        }
        foreach ([['Metro Builders Supply', 'Building Materials, Hardware & Fixtures'], ['ColorPro Trading', 'Architectural Finishes'],
            ['SealTech Industrial', 'Specialized Products']] as [$n,$c]) {
            Supplier::firstOrCreate(['name' => $n], ['categories_supplied' => $c, 'phone' => '0917-000-0000']);
        }

        $samples = [['Juan Dela Cruz', 'quotation', [[0, 10], [1, 20]], 'none', 0],
            ['Santos Construction', 'pending_approval', [[2, 50]], 'percentage', 5],
            ['Reyes Realty', 'confirmed', [[3, 6], [7, 4]], 'fixed', 300],
            ['Lim Hardware', 'confirmed', [[5, 20], [6, 5]], 'none', 0]];
        foreach ($samples as $i => [$cust,$st,$lines,$dt,$dv]) {
            $o = Order::create(['order_number' => sprintf('ORD-%d-%04d', now()->year, $i + 1), 'customer_name' => $cust,
                'customer_company' => $cust, 'customer_contact' => '0918-111-000'.$i, 'delivery_address' => 'Davao City',
                'status' => $st, 'discount_type' => $dt, 'discount_value' => $dv, 'created_by' => $admin->id,
                'approved_by' => $st === 'confirmed' ? $admin->id : null, 'approved_at' => $st === 'confirmed' ? now() : null]);
            foreach ($lines as [$pi,$qty]) {
                OrderItem::create(['order_id' => $o->id, 'product_id' => $made[$pi]->id,
                    'quantity' => $qty, 'quoted_price' => $made[$pi]->unit_price]);
            }
        }
    }
}
