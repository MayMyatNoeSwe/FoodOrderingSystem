<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // =========================================================
        // 1. ACCOUNTS
        // =========================================================
        $admin = User::firstOrCreate(
            ['email' => 'admin@foodorder.com'],
            [
                'name'     => 'System Admin',
                'password' => bcrypt('password'),
                'role'     => 'admin',
            ]
        );

        // Riders
        $ridersData = [
            ['name' => 'Speedy Rider',          'email' => 'rider@foodorder.com',                'phone' => '09988776655', 'city' => 'Yangon'],
            ['name' => 'Aung Aung (Rider)',      'email' => 'aungaung.rider@foodorder.com',      'phone' => '09951234567', 'city' => 'Yangon'],
            ['name' => 'Kyaw Kyaw (Rider)',      'email' => 'kyawkyaw.rider@foodorder.com',      'phone' => '09952345678', 'city' => 'Yangon'],
            ['name' => 'Min Min (Rider)',         'email' => 'minmin.rider@foodorder.com',         'phone' => '09953456789', 'city' => 'Mandalay'],
            ['name' => 'Zaw Zaw (Rider)',         'email' => 'zawzaw.rider@foodorder.com',         'phone' => '09954567890', 'city' => 'Yangon'],
            ['name' => 'Tun Tun (Rider)',         'email' => 'tuntun.rider@foodorder.com',         'phone' => '09955678901', 'city' => 'Yangon'],
            ['name' => 'Win Win (Rider)',          'email' => 'winwin.rider@foodorder.com',          'phone' => '09956789012', 'city' => 'Nay Pyi Taw'],
            ['name' => 'Thura (Rider)',            'email' => 'thura.rider@foodorder.com',            'phone' => '09957890123', 'city' => 'Yangon'],
            ['name' => 'Ko Ko (Rider)',            'email' => 'koko.rider@foodorder.com',             'phone' => '09958901234', 'city' => 'Mandalay'],
            ['name' => 'Htet Htet (Rider)',        'email' => 'htethtet.rider@foodorder.com',         'phone' => '09959012345', 'city' => 'Yangon'],
        ];

        foreach ($ridersData as $rData) {
            User::firstOrCreate(
                ['email' => $rData['email']],
                [
                    'name'         => $rData['name'],
                    'password'     => bcrypt('password'),
                    'role'         => 'rider',
                    'phone_number' => $rData['phone'],
                    'city'         => $rData['city'],
                ]
            );
        }

        // Shop Owners
        $shopOwner1 = User::firstOrCreate(
            ['email' => 'owner1@pizzapalace.com'],
            [
                'name'     => 'Ko Zin Latt',
                'password' => bcrypt('password'),
                'role'     => 'shop_owner',
            ]
        );

        $shopOwner2 = User::firstOrCreate(
            ['email' => 'owner2@burgerstation.com'],
            [
                'name'     => 'Ma Hnin Wai',
                'password' => bcrypt('password'),
                'role'     => 'shop_owner',
            ]
        );

        $shopOwner3 = User::firstOrCreate(
            ['email' => 'owner3@noodlehouse.com'],
            [
                'name'     => 'U Thant Zin',
                'password' => bcrypt('password'),
                'role'     => 'shop_owner',
            ]
        );

        // Customers
        $customer1 = User::firstOrCreate(
            ['email' => 'user@example.com'],
            ['name' => 'John Doe',      'password' => bcrypt('password'), 'role' => 'user']
        );
        $customer2 = User::firstOrCreate(
            ['email' => 'sarah@example.com'],
            ['name' => 'Sarah Connor',  'password' => bcrypt('password'), 'role' => 'user']
        );
        $customer3 = User::firstOrCreate(
            ['email' => 'michael@example.com'],
            ['name' => 'Michael Scott', 'password' => bcrypt('password'), 'role' => 'user']
        );

        // =========================================================
        // 2. SHOPS
        // =========================================================
        MenuItem::query()->delete();
        Category::query()->delete();
        Shop::query()->delete();

        $shop1 = Shop::create([
            'name'        => 'Pizza Palace',
            'slug'        => 'pizza-palace',
            'description' => 'Artisan Neapolitan pizzas, cheesy burgers & refreshing drinks. The best dine-in experience in Kamayut.',
            'address'     => 'No. 45, Pyay Road, Kamayut Township, Yangon',
            'phone'       => '09788001122',
            'email'       => 'hello@pizzapalace.com',
            'status'      => 'active',
            'owner_id'    => $shopOwner1->id,
        ]);

        $shop2 = Shop::create([
            'name'        => 'Burger Station',
            'slug'        => 'burger-station',
            'description' => 'Juicy handcrafted beef & chicken burgers with crispy fries and cold beverages. Yangon\'s #1 burger spot.',
            'address'     => 'No. 12, Bogyoke Aung San Road, Pabedan Township, Yangon',
            'phone'       => '09788003344',
            'email'       => 'info@burgerstation.com',
            'status'      => 'active',
            'owner_id'    => $shopOwner2->id,
        ]);

        $shop3 = Shop::create([
            'name'        => 'Noodle House',
            'slug'        => 'noodle-house',
            'description' => 'Authentic Asian noodles — ramen, pad thai, pho & more. Warm broths and premium ingredients every day.',
            'address'     => 'No. 78, 37th Street, Kyauktada Township, Yangon',
            'phone'       => '09788005566',
            'email'       => 'support@noodlehouse.com',
            'status'      => 'active',
            'owner_id'    => $shopOwner3->id,
        ]);

        // =========================================================
        // 3. CATEGORIES & MENU ITEMS (shop-scoped)
        // =========================================================
        $shopsData = [
            // ---- PIZZA PALACE ----
            [
                'shop'  => $shop1,
                'categories' => [
                    [
                        'name' => 'Pizza',
                        'slug' => 'pizza',
                        'items' => [
                            ['name' => 'Pepperoni Passion Pizza',     'description' => 'Double pepperoni with extra mozzarella cheese & rich tomato sauce', 'price' => 18000, 'image' => '/images/pizza.png'],
                            ['name' => 'Supreme Meat Lovers',         'description' => 'Pepperoni, Italian sausage, beef, bacon, and mozzarella',           'price' => 19500, 'image' => '/images/pizza.png'],
                            ['name' => 'Cheesy Garlic Crust Pizza',   'description' => 'Garlic butter crust loaded with 4 gourmet cheeses',                 'price' => 15000, 'image' => '/images/pizza.png'],
                            ['name' => 'BBQ Chicken Deluxe Pizza',    'description' => 'Grilled chicken breast, red onions, cilantro with tangy BBQ sauce',  'price' => 17500, 'image' => '/images/pizza.png'],
                            ['name' => 'Hawaiian Paradise Pizza',     'description' => 'Smoked ham, sweet pineapple chunks, and mozzarella cheese',          'price' => 16000, 'image' => '/images/pizza.png'],
                            ['name' => 'Veggie Feast Pizza',          'description' => 'Bell peppers, mushrooms, red onions, olives, and fresh tomatoes',     'price' => 14000, 'image' => '/images/pizza.png'],
                            ['name' => 'Four Cheese Special Pizza',   'description' => 'Mozzarella, cheddar, parmesan, and gorgonzola blend',                 'price' => 16500, 'image' => '/images/pizza.png'],
                            ['name' => 'Spicy Seafood Supreme',       'description' => 'Shrimp, calamari, chili flakes, garlic oil, and herbs',              'price' => 20000, 'image' => '/images/pizza.png'],
                            ['name' => 'Truffle Mushroom Special',    'description' => 'Wild mushrooms, truffle cream sauce, and fresh arugula',             'price' => 20000, 'image' => '/images/pizza.png'],
                            ['name' => 'Margherita Classic Pizza',    'description' => 'San Marzano tomatoes, fresh basil, and buffalo mozzarella',           'price' => 13500, 'image' => '/images/pizza.png'],
                            ['name' => 'Bacon & Cheddar Slice Pizza', 'description' => 'Crispy bacon strips, sharp cheddar cheese, and green onions',         'price' => 15000, 'image' => '/images/pizza.png'],
                            ['name' => 'Buffalo Chicken Pizza',       'description' => 'Spicy buffalo chicken, ranch drizzle, and melted mozzarella',         'price' => 17000, 'image' => '/images/pizza.png'],
                        ],
                    ],
                    [
                        'name' => 'Desserts',
                        'slug' => 'desserts-pizza-palace',
                        'items' => [
                            ['name' => 'Triple Chocolate Brownie',  'description' => 'Warm fudgy brownie with vanilla ice cream & chocolate sauce',          'price' => 8500, 'image' => '/images/desserts.png'],
                            ['name' => 'New York Cheesecake',       'description' => 'Classic rich cheesecake with fresh raspberry compote',                 'price' => 9000, 'image' => '/images/desserts.png'],
                            ['name' => 'Molten Chocolate Lava Cake','description' => 'Warm chocolate cake with gooey molten lava center',                    'price' => 9500, 'image' => '/images/desserts.png'],
                        ],
                    ],
                    [
                        'name' => 'Drinks',
                        'slug' => 'drinks-pizza-palace',
                        'items' => [
                            ['name' => 'Cold Brew Nitro Coffee',      'description' => 'Smooth 18-hour cold brew infused with nitrogen',                     'price' => 6000, 'image' => '/images/beverages.png'],
                            ['name' => 'Fresh Mango Smoothie',        'description' => 'Blend of sweet ripe mangoes, passionfruit and Greek yogurt',         'price' => 7000, 'image' => '/images/beverages.png'],
                            ['name' => 'Sparkling Berry Lemonade',    'description' => 'Freshly squeezed lemons with muddled wild berries & soda',           'price' => 5000, 'image' => '/images/beverages.png'],
                        ],
                    ],
                ],
            ],

            // ---- BURGER STATION ----
            [
                'shop'  => $shop2,
                'categories' => [
                    [
                        'name' => 'Burgers',
                        'slug' => 'burgers-burger-station',
                        'items' => [
                            ['name' => 'Double Bacon Cheeseburger',  'description' => 'Two Angus beef patties, crispy bacon, cheddar, lettuce & special sauce', 'price' => 14500, 'image' => '/images/burger.png'],
                            ['name' => 'Classic Beef Deluxe Burger', 'description' => 'Juicy beef patty, cheddar cheese, pickles, onions & tomato',             'price' => 12000, 'image' => '/images/burger.png'],
                            ['name' => 'Spicy Zinger Chicken Burger','description' => 'Crispy fried chicken breast, spicy mayo, and crunchy slaw',              'price' => 12500, 'image' => '/images/burger.png'],
                            ['name' => 'Mushroom Swiss Burger',      'description' => 'Sautéed mushrooms, melted Swiss cheese, and garlic aioli',               'price' => 14000, 'image' => '/images/burger.png'],
                            ['name' => 'BBQ Pulled Pork Burger',     'description' => 'Slow-cooked pulled pork, BBQ sauce, and crispy onion rings',             'price' => 15000, 'image' => '/images/burger.png'],
                            ['name' => 'Veggie Black Bean Burger',   'description' => 'Housemade black bean patty, avocado, lettuce & chipotle sauce',         'price' => 11000, 'image' => '/images/burger.png'],
                        ],
                    ],
                    [
                        'name' => 'Beverages',
                        'slug' => 'beverages-burger-station',
                        'items' => [
                            ['name' => 'Strawberry Milkshake',     'description' => 'Real strawberry ice cream topped with whipped cream & cherry',   'price' => 6500, 'image' => '/images/beverages.png'],
                            ['name' => 'Iced Matcha Latte',        'description' => 'Premium Uji matcha whisked with cold oat milk and honey',        'price' => 6500, 'image' => '/images/beverages.png'],
                            ['name' => 'Thai Iced Tea with Boba',  'description' => 'Sweet spiced Thai black tea with condensed milk & boba pearls',  'price' => 5500, 'image' => '/images/beverages.png'],
                        ],
                    ],
                ],
            ],

            // ---- NOODLE HOUSE ----
            [
                'shop'  => $shop3,
                'categories' => [
                    [
                        'name' => 'Noodles',
                        'slug' => 'noodles-noodle-house',
                        'items' => [
                            ['name' => 'Spicy Seafood Ramen',         'description' => 'Rich spicy miso broth, prawns, squid, soft-boiled egg & ramen noodles', 'price' => 15000, 'image' => '/images/noodles.png'],
                            ['name' => 'Pad Thai Special Noodles',    'description' => 'Stir-fried rice noodles with prawns, tofu, peanuts & bean sprouts',      'price' => 13500, 'image' => '/images/noodles.png'],
                            ['name' => 'Beef Pho Noodle Soup',        'description' => 'Traditional Vietnamese aromatic beef broth with rice noodles & herbs',   'price' => 14000, 'image' => '/images/noodles.png'],
                            ['name' => 'Chow Mein Special',           'description' => 'Crispy egg noodles with chicken, vegetables & savory soy sauce',         'price' => 12500, 'image' => '/images/noodles.png'],
                            ['name' => 'Singapore Fried Vermicelli',  'description' => 'Curry-infused rice vermicelli with shrimp, pork & bell peppers',         'price' => 13000, 'image' => '/images/noodles.png'],
                            ['name' => 'Dan Dan Spicy Noodles',       'description' => 'Sichuan wheat noodles with minced pork, chili oil & sesame paste',       'price' => 12000, 'image' => '/images/noodles.png'],
                        ],
                    ],
                    [
                        'name' => 'Desserts',
                        'slug' => 'desserts-noodle-house',
                        'items' => [
                            ['name' => 'Italian Tiramisu Slice',   'description' => 'Espresso-soaked ladyfingers layered with mascarpone cream', 'price' => 8500, 'image' => '/images/desserts.png'],
                            ['name' => 'Matcha Ice Cream Parfait', 'description' => 'Green tea gelato, red bean paste, and crispy waffle cone chips', 'price' => 6000, 'image' => '/images/desserts.png'],
                        ],
                    ],
                    [
                        'name' => 'Drinks',
                        'slug' => 'drinks-noodle-house',
                        'items' => [
                            ['name' => 'Fresh Lychee Soda',        'description' => 'Chilled lychee juice with soda water and fresh mint leaves',   'price' => 4500, 'image' => '/images/beverages.png'],
                            ['name' => 'Iced Jasmine Green Tea',   'description' => 'Delicate floral jasmine tea served over ice with honey',       'price' => 4000, 'image' => '/images/beverages.png'],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($shopsData as $shopDef) {
            $shop = $shopDef['shop'];
            foreach ($shopDef['categories'] as $catInfo) {
                $category = Category::create([
                    'shop_id' => $shop->id,
                    'name'    => $catInfo['name'],
                    'slug'    => $catInfo['slug'],
                ]);

                foreach ($catInfo['items'] as $itemInfo) {
                    MenuItem::create([
                        'shop_id'     => $shop->id,
                        'category_id' => $category->id,
                        'name'        => $itemInfo['name'],
                        'description' => $itemInfo['description'],
                        'price'       => $itemInfo['price'],
                        'image'       => $itemInfo['image'],
                        'stock'       => rand(10, 75),
                        'is_available'=> true,
                    ]);
                }
            }
        }

        // =========================================================
        // 4. SAMPLE ORDERS (one per shop)
        // =========================================================
        Order::query()->delete();
        OrderItem::query()->delete();

        $customers    = [$customer1, $customer2, $customer3];
        $paymentMethods = ['kbzpay', 'wavepay', 'cod'];
        $statuses     = ['preparing', 'delivering', 'completed', 'cancelled', 'pending', 'confirmed'];
        $shops        = [$shop1, $shop2, $shop3];

        foreach ($shops as $shop) {
            $shopItems = MenuItem::where('shop_id', $shop->id)->get();
            if ($shopItems->isEmpty()) {
                continue;
            }

            for ($i = 1; $i <= 6; $i++) {
                $user          = $customers[array_rand($customers)];
                $status        = $statuses[array_rand($statuses)];
                $paymentMethod = $paymentMethods[array_rand($paymentMethods)];

                $randomItems = $shopItems->count() >= 3
                    ? $shopItems->random(rand(2, min(3, $shopItems->count())))
                    : $shopItems;

                $subtotal    = $randomItems->sum('price');
                $deliveryFee = 1500;
                $totalAmount = $subtotal + $deliveryFee;

                $order = Order::create([
                    'order_number'    => 'ORD-' . strtoupper(Str::random(8)),
                    'user_id'         => $user->id,
                    'shop_id'         => $shop->id,
                    'total_amount'    => $totalAmount,
                    'delivery_fee'    => $deliveryFee,
                    'delivery_address'=> 'No. ' . rand(10, 150) . ', Pyay Road, Kamayut Township, Yangon',
                    'delivery_phone'  => '09' . rand(10000000, 99999999),
                    'status'          => $status,
                    'payment_method'  => $paymentMethod,
                    'payment_status'  => ($paymentMethod === 'cod') ? 'unpaid' : 'paid',
                    'created_at'      => now()->subHours(rand(1, 72)),
                ]);

                foreach ($randomItems as $item) {
                    $qty = rand(1, 2);
                    OrderItem::create([
                        'order_id'    => $order->id,
                        'menu_item_id'=> $item->id,
                        'quantity'    => $qty,
                        'unit_price'  => $item->price,
                        'subtotal'    => $item->price * $qty,
                    ]);
                }
            }
        }
    }
}
