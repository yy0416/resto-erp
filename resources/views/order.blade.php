<!doctype html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Order</title>
    @vite('resources/css/app.css')
    <script defer src="https://unpkg.com/alpinejs"></script>
</head>

<body class="bg-gray-100 p-4">
    <div x-data="orderApp()" class="max-w-md mx-auto bg-white p-4 rounded shadow">

        <h1 class="text-xl font-bold mb-4">📱 Passer une commande</h1>

        <input x-model="phone" placeholder="Téléphone"
            class="border p-2 w-full mb-2">

        <input x-model="name" placeholder="Nom"
            class="border p-2 w-full mb-4">

        <template x-for="dish in dishes">
            <div class="flex justify-between items-center mb-2">
                <span x-text="dish.name"></span>
                <div>
                    <button @click="dish.qty--" class="px-2">-</button>
                    <span x-text="dish.qty"></span>
                    <button @click="dish.qty++" class="px-2">+</button>
                </div>
            </div>
        </template>

        <button @click="submit"
            class="bg-green-600 text-white w-full py-2 mt-4 rounded">
            Commander
        </button>

        <p x-text="message" class="mt-2 text-green-600"></p>
    </div>

    <script>
        function orderApp() {
            return {
                phone: '',
                name: '',
                message: '',
                dishes: [{
                        id: 3,
                        name: 'Burger',
                        qty: 0
                    },
                    {
                        id: 4,
                        name: 'Frites',
                        qty: 0
                    },
                ],

                submit() {
                    fetch('/api/orders', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                phone: this.phone,
                                name: this.name,
                                restaurant_id: 1,
                                items: this.dishes
                                    .filter(d => d.qty > 0)
                                    .map(d => ({
                                        dish_id: d.id,
                                        quantity: d.qty
                                    }))
                            })
                        })
                        .then(r => r.json())
                        .then(() => this.message = '✅ Commande envoyée')
                        .catch(() => this.message = '❌ Erreur');
                }
            }
        }
    </script>
</body>

</html>