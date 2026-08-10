<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
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
        // 1. Seed Accounts (Admin & Customer Users)
        $admin = User::firstOrCreate(
            ['email' => 'admin@foodorder.com'],
            [
                'name' => 'System Admin',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]
        );

        $customer1 = User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'John Doe',
                'password' => bcrypt('password'),
                'role' => 'user',
            ]
        );

        $customer2 = User::firstOrCreate(
            ['email' => 'sarah@example.com'],
            [
                'name' => 'Sarah Connor',
                'password' => bcrypt('password'),
                'role' => 'user',
            ]
        );

        $customer3 = User::firstOrCreate(
            ['email' => 'michael@example.com'],
            [
                'name' => 'Michael Scott',
                'password' => bcrypt('password'),
                'role' => 'user',
            ]
        );

        // 2. Seed Real Food Categories
        $categoriesData = [
            [
                'name' => 'Pizza',
                'slug' => 'pizza',
                'description' => 'Cheesy & crispy artisan pizzas',
                'icon' => '🍕',
                'image' => '/images/pizza.png',
                'items' => [
                    ['name' => 'Pepperoni Passion Pizza', 'description' => 'Double pepperoni with extra mozzarella cheese & rich tomato sauce', 'price' => 18000, 'image' => '/images/pizza.png'],
                    ['name' => 'Supreme Meat Lovers', 'description' => 'Pepperoni, Italian sausage, beef, bacon, and mozzarella', 'price' => 19500, 'image' => '/images/pizza.png'],
                    ['name' => 'Cheesy Garlic Crust Pizza', 'description' => 'Garlic butter crust loaded with 4 gourmet cheeses', 'price' => 15000, 'image' => '/images/pizza.png'],
                    ['name' => 'BBQ Chicken Deluxe Pizza', 'description' => 'Grilled chicken breast, red onions, cilantro with tangy BBQ sauce', 'price' => 17500, 'image' => '/images/pizza.png'],
                    ['name' => 'Hawaiian Paradise Pizza', 'description' => 'Smoked ham, sweet pineapple chunks, and mozzarella cheese', 'price' => 16000, 'image' => '/images/pizza.png'],
                    ['name' => 'Veggie Feast Pizza', 'description' => 'Bell peppers, mushrooms, red onions, olives, and fresh tomatoes', 'price' => 14000, 'image' => '/images/pizza.png'],
                    ['name' => 'Four Cheese Special Pizza', 'description' => 'Mozzarella, cheddar, parmesan, and gorgonzola blend', 'price' => 16500, 'image' => '/images/pizza.png'],
                    ['name' => 'Spicy Seafood Supreme', 'description' => 'Shrimp, calamari, chili flakes, garlic oil, and herbs', 'price' => 20000, 'image' => '/images/pizza.png'],
                    ['name' => 'Truffle Mushroom Special', 'description' => 'Wild mushrooms, truffle cream sauce, and fresh arugula', 'price' => 20000, 'image' => '/images/pizza.png'],
                    ['name' => 'Margherita Classic Pizza', 'description' => 'San Marzano tomatoes, fresh basil, and buffalo mozzarella', 'price' => 13500, 'image' => '/images/pizza.png'],
                    ['name' => 'Bacon & Cheddar Slice Pizza', 'description' => 'Crispy bacon strips, sharp cheddar cheese, and green onions', 'price' => 15000, 'image' => '/images/pizza.png'],
                    ['name' => 'Buffalo Chicken Pizza', 'description' => 'Spicy buffalo chicken, ranch drizzle, and melted mozzarella', 'price' => 17000, 'image' => '/images/pizza.png'],
                ]
            ],
            [
                'name' => 'Burgers',
                'slug' => 'burgers',
                'description' => 'Juicy handcrafted beef & chicken burgers',
                'icon' => '🍔',
                'image' => '/images/burger.png',
                'items' => [
                    ['name' => 'Double Bacon Cheeseburger', 'description' => 'Two Angus beef patties, crispy bacon, cheddar, lettuce & special sauce', 'price' => 14500, 'image' => '/images/burger.png'],
                    ['name' => 'Classic Beef Deluxe Burger', 'description' => 'Juicy beef patty, cheddar cheese, pickles, onions & tomato', 'price' => 12000, 'image' => '/images/burger.png'],
                    ['name' => 'Spicy Zinger Chicken Burger', 'description' => 'Crispy fried chicken breast, spicy mayo, and crunchy slaw', 'price' => 12500, 'image' => '/images/burger.png'],
                    ['name' => 'Mushroom Swiss Burger', 'description' => 'Sautéed mushrooms, melted Swiss cheese, and garlic aioli', 'price' => 14000, 'image' => '/images/burger.png'],
                    ['name' => 'BBQ Pulled Pork Burger', 'description' => 'Slow-cooked pulled pork, BBQ sauce, and crispy onion rings', 'price' => 15000, 'image' => '/images/burger.png'],
                    ['name' => 'Veggie Black Bean Burger', 'description' => 'Housemade black bean patty, avocado, lettuce & chipotle sauce', 'price' => 11000, 'image' => '/images/burger.png'],
                ]
            ],
            [
                'name' => 'Noodles',
                'slug' => 'noodles',
                'description' => 'Authentic Asian stir-fried & soup noodles',
                'icon' => '🍜',
                'image' => '/images/noodles.png',
                'items' => [
                    ['name' => 'Spicy Seafood Ramen', 'description' => 'Rich spicy miso broth, prawns, squid, soft-boiled egg & ramen noodles', 'price' => 15000, 'image' => '/images/noodles.png'],
                    ['name' => 'Pad Thai Special Noodles', 'description' => 'Stir-fried rice noodles with prawns, tofu, peanuts & bean sprouts', 'price' => 13500, 'image' => '/images/noodles.png'],
                    ['name' => 'Beef Pho Noodle Soup', 'description' => 'Traditional Vietnamese aromatic beef broth with rice noodles & herbs', 'price' => 14000, 'image' => '/images/noodles.png'],
                    ['name' => 'Chow Mein Special', 'description' => 'Crispy egg noodles with chicken, vegetables & savory soy sauce', 'price' => 12500, 'image' => '/images/noodles.png'],
                    ['name' => 'Singapore Fried Vermicelli', 'description' => 'Curry-infused rice vermicelli with shrimp, pork & bell peppers', 'price' => 13000, 'image' => '/images/noodles.png'],
                    ['name' => 'Dan Dan Spicy Noodles', 'description' => 'Sichuan wheat noodles with minced pork, chili oil & sesame paste', 'price' => 12000, 'image' => '/images/noodles.png'],
                ]
            ],
            [
                'name' => 'Beverages',
                'slug' => 'beverages',
                'description' => 'Refreshing smoothies, coffees & cold drinks',
                'icon' => '🍹',
                'image' => '/images/beverages.png',
                'items' => [
                    ['name' => 'Fresh Mango Tropical Smoothie', 'description' => 'Blend of sweet ripe mangoes, passionfruit, and Greek yogurt', 'price' => 7000, 'image' => '/images/beverages.png'],
                    ['name' => 'Iced Matcha Green Tea Latte', 'description' => 'Premium Uji matcha whisked with cold oat milk and honey', 'price' => 6500, 'image' => '/images/beverages.png'],
                    ['name' => 'Sparkling Berry Lemonade', 'description' => 'Freshly squeezed lemons with muddled wild berries & soda', 'price' => 5000, 'image' => '/images/beverages.png'],
                    ['name' => 'Cold Brew Nitro Coffee', 'description' => 'Smooth 18-hour cold brew coffee infused with nitrogen', 'price' => 6000, 'image' => '/images/beverages.png'],
                    ['name' => 'Strawberry Milkshake Supreme', 'description' => 'Real strawberry ice cream topped with whipped cream & cherry', 'price' => 6500, 'image' => '/images/beverages.png'],
                    ['name' => 'Thai Iced Tea with Boba', 'description' => 'Sweet spiced Thai black tea with condensed milk & tapioca pearls', 'price' => 5500, 'image' => '/images/beverages.png'],
                ]
            ],
            [
                'name' => 'Desserts',
                'slug' => 'desserts',
                'description' => 'Decadent cakes, tarts & sweet treats',
                'icon' => '🍰',
                'image' => '/images/desserts.png',
                'items' => [
                    ['name' => 'Triple Chocolate Brownie', 'description' => 'Warm fudgy brownie served with vanilla bean ice cream & chocolate sauce', 'price' => 8500, 'image' => '/images/desserts.png'],
                    ['name' => 'New York Creamy Cheesecake', 'description' => 'Classic rich cheesecake with fresh raspberry compote', 'price' => 9000, 'image' => '/images/desserts.png'],
                    ['name' => 'Italian Tiramisu Slice', 'description' => 'Espresso-soaked ladyfingers layered with mascarpone cream', 'price' => 8500, 'image' => '/images/desserts.png'],
                    ['name' => 'Fresh Strawberry Fruit Tart', 'description' => 'Butter pastry shell filled with vanilla custard & sweet berries', 'price' => 7500, 'image' => '/images/desserts.png'],
                    ['name' => 'Molten Chocolate Lava Cake', 'description' => 'Warm chocolate cake with gooey molten lava center', 'price' => 9500, 'image' => '/images/desserts.png'],
                    ['name' => 'Matcha Ice Cream Parfait', 'description' => 'Green tea gelato, red bean paste, and crispy waffle cone chips', 'price' => 6000, 'image' => '/images/desserts.png'],
                ]
            ]
        ];

        // Clean existing menu items and categories to prevent duplicates
        MenuItem::query()->delete();
        Category::query()->delete();

        foreach ($categoriesData as $catInfo) {
            $category = Category::create([
                'name' => $catInfo['name'],
                'slug' => $catInfo['slug'],
            ]);


            foreach ($catInfo['items'] as $itemInfo) {
                MenuItem::create([
                    'category_id' => $category->id,
                    'name' => $itemInfo['name'],
                    'description' => $itemInfo['description'],
                    'price' => $itemInfo['price'],
                    'image' => $itemInfo['image'],
                    'is_available' => true,
                ]);
            }
        }

        // 3. Seed Realistic Orders
        Order::query()->delete();
        OrderItem::query()->delete();

        $customers = [$customer1, $customer2, $customer3];
        $paymentMethods = ['kbzpay', 'wavepay', 'cod'];
        $statuses = ['preparing', 'delivering', 'completed', 'cancelled', 'pending', 'confirmed'];


        $allMenuItems = MenuItem::all();

        for ($i = 1; $i <= 18; $i++) {
            $user = $customers[array_rand($customers)];
            $status = $statuses[array_rand($statuses)];
            $paymentMethod = $paymentMethods[array_rand($paymentMethods)];
            
            $randomItems = $allMenuItems->random(rand(2, 4));
            $subtotal = $randomItems->sum('price');
            $deliveryFee = 1500;
            $totalAmount = $subtotal + $deliveryFee;

            $order = Order::create([
                'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                'user_id' => $user->id,
                'total_amount' => $totalAmount,
                'delivery_fee' => $deliveryFee,
                'delivery_address' => 'No. ' . rand(10, 150) . ', Pyay Road, Kamayut Township, Yangon',
                'delivery_phone' => '09' . rand(10000000, 99999999),
                'status' => $status,
                'payment_method' => $paymentMethod,
                'payment_status' => ($status === 'completed') ? 'paid' : 'unpaid',

                'created_at' => now()->subHours(rand(1, 48)),
            ]);

            foreach ($randomItems as $item) {
                $qty = rand(1, 2);
                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $item->id,
                    'quantity' => $qty,
                    'unit_price' => $item->price,
                    'subtotal' => $item->price * $qty,
                ]);
            }

        }
    }
}
