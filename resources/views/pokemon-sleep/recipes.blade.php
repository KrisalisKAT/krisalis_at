<script>
    const recipes = {
        Curry: [
            {
                name: 'Mixed Curry',
                ingredients: {},
                catchAll: true,
            },
            {
                name: 'Fancy Apple Curry',
                ingredients: {
                    Apple: 7,
                },
            },
            {
                name: 'Grilled Tail Curry',
                ingredients: {
                    Tail: 8,
                    Herb: 25,
                },
            },
            {
                name: 'Solar Power Tomato Curry',
                ingredients: {
                    Tomato: 10,
                    Herb: 5,
                },
            },
            {
                name: 'Dream Eater Butter Curry',
                ingredients: {
                    Potato: 18,
                    Tomato: 15,
                    Cacao: 12,
                    Milk: 10,
                },
            },
            {
                name: 'Spicy Leek Curry',
                ingredients: {
                    Leek: 14,
                    Ginger: 10,
                    Herb: 8,
                },
            },
            {
                name: 'Spore Mushroom Curry',
                ingredients: {
                    Mushroom: 14,
                    Potato: 9,
                },
            },
            {
                name: 'Egg Bomb Curry',
                ingredients: {
                    Honey: 12,
                    Apple: 11,
                    Egg: 8,
                    Potato: 4,
                },
            },
            {
                name: 'Hearty Cheeseburger Curry',
                ingredients: {
                    Milk: 8,
                    Sausage: 8
                },
            },
            {
                name: 'Soft Potato Chowder',
                ingredients: {
                    Milk: 10,
                    Potato: 8,
                    Mushroom: 4,
                },
            },
            {
                name: 'Simple Chowder',
                ingredients: {
                    Milk: 7,
                },
            },
            {
                name: 'Beanburger Curry',
                ingredients: {
                    Sausage: 7
                },
            },
            {
                name: 'Mild Honey Curry',
                ingredients: {
                    Honey: 7,
                },
            },
            {
                name: 'Ninja Curry',
                ingredients: {
                    Soybeans: 24,
                    Sausage: 9,
                    Leek: 12,
                    Mushroom: 5,
                },
            },
            {
                name: 'Drought Katsu Curry',
                ingredients: {
                    Sausage: 10,
                    Oil: 5,
                },
            },
            {
                name: 'Melty Omelette Curry',
                ingredients: {
                    Egg: 10,
                    Tomato: 6,
                },
            },
            {
                name: 'Bulk Up Bean Curry',
                ingredients: {
                    Soybeans: 12,
                    Sausage: 6,
                    Herb: 4,
                    Egg: 4,
                },
            },
            {
                name: 'Limber Corn Stew',
                ingredients: {
                    Corn: 14,
                    Milk: 8,
                    Potato: 8,
                },
            },
            {
                name: 'Inferno Corn Keema Curry',
                ingredients: {
                    Herb: 27,
                    Sausage: 24,
                    Corn: 14,
                    Ginger: 12,
                },
            },
            {
                name: 'Dizzy Punch Spicy Curry',
                ingredients: {
                    Coffee: 11,
                    Herb: 11,
                    Honey: 11,
                },
            },
            {
                name: 'Hidden Power Perk-Up Stew',
                ingredients: {
                    Soybeans: 28,
                    Tomato: 25,
                    Mushroom: 23,
                    Coffee: 16,
                },
            },
        ],
        Salad: [
            {
                name: 'Mixed Salad',
                ingredients: {},
                catchAll: true,
            },
            {
                name: 'Slowpoke Tail Pepper Salad',
                ingredients: {
                    Tail: 10,
                    Herb: 10,
                    Oil: 15,
                },
            },
            {
                name: 'Spore Mushroom Salad',
                ingredients: {
                    Mushroom: 17,
                    Tomato: 8,
                    Oil: 8,
                },
            },
            {
                name: 'Snow Cloak Caesar Salad',
                ingredients: {
                    Milk: 10,
                    Sausage: 6,
                },
            },
            {
                name: 'Gluttony Potato Salad',
                ingredients: {
                    Potato: 14,
                    Egg: 9,
                    Sausage: 7,
                    Apple: 6,
                },
            },
            {
                name: 'Water Veil Tofu Salad',
                ingredients: {
                    Soybeans: 15,
                    Tomato: 9,
                },
            },
            {
                name: 'Superpower Extreme Salad',
                ingredients: {
                    Sausage: 9,
                    Ginger: 6,
                    Egg: 5,
                    Potato: 3,
                },
            },
            {
                name: 'Bean Ham Salad',
                ingredients: {
                    Sausage: 8,
                },
            },
            {
                name: 'Snoozy Tomato Salad',
                ingredients: {
                    Tomato: 8,
                },
            },
            {
                name: 'Moomoo Caprese Salad',
                ingredients: {
                    Milk: 12,
                    Tomato: 6,
                    Oil: 5,
                },
            },
            {
                name: 'Contrary Chocolate Meat Salad',
                ingredients: {
                    Cacao: 14,
                    Sausage: 9,
                },
            },
            {
                name: 'Overheat Ginger Salad',
                ingredients: {
                    Herb: 17,
                    Ginger: 10,
                    Tomato: 8,
                },
            },
            {
                name: 'Fancy Apple Salad',
                ingredients: {
                    Apple: 8,
                },
            },
            {
                name: 'Immunity Leek Salad',
                ingredients: {
                    Leek: 10,
                    Ginger: 5,
                },
            },
            {
                name: 'Dazzling Apple Cheese Salad',
                ingredients: {
                    Apple: 15,
                    Milk: 5,
                    Oil: 3,
                },
            },
            {
                name: 'Ninja Salad',
                ingredients: {
                    Leek: 15,
                    Soybeans: 19,
                    Mushroom: 12,
                    Ginger: 11
                },
            },
            {
                name: 'Heat Wave Tofu Salad',
                ingredients: {
                    Soybeans: 10,
                    Herb: 6,
                },
            },
            {
                name: 'Greengrass Salad',
                ingredients: {
                    Oil: 22,
                    Corn: 17,
                    Tomato: 14,
                    Potato: 9,
                },
            },
            {
                name: 'Calm Mind Fruit Salad',
                ingredients: {
                    Apple: 21,
                    Honey: 16,
                    Corn: 12,
                },
            },
            {
                name: 'Fury Attack Corn Salad',
                ingredients: {
                    Corn: 9,
                    Oil: 8,
                },
            },
            {
                name: 'Cross Chop Salad',
                ingredients: {
                    Egg: 20,
                    Sausage: 15,
                    Corn: 11,
                    Tomato: 10,
                },
            },
            {
                name: 'Defiant Coffee-Dressed Salad',
                ingredients: {
                    Coffee: 28,
                    Sausage: 28,
                    Oil: 22,
                    Potato: 22,
                },
            },
        ],
        Dessert: [
            {
                name: 'Mixed Juice',
                ingredients: {},
                catchAll: true,
            },
            {
                name: 'Fluffy Sweet Potatoes',
                ingredients: {
                    Potato: 9,
                    MilkL: 5,
                },
            },
            {
                name: 'Steadfast Ginger Cookies',
                ingredients: {
                    Honey: 14,
                    Ginger: 12,
                    Cacao: 5,
                    Egg: 4,
                },
            },
            {
                name: 'Fancy Apple Juice',
                ingredients: {
                    Apple: 8,
                },
            },
            {
                name: 'Craft Soda Pop',
                ingredients: {
                    Honey: 9,
                },
            },
            {
                name: 'Ember Ginger Tea',
                ingredients: {
                    Ginger: 9,
                    Apple: 7,
                },
            },
            {
                name: 'Jigglypuff\'s Fruity Flan',
                ingredients: {
                    Honey: 20,
                    Egg: 15,
                    Milk: 10,
                    Apple: 10,
                },
            },
            {
                name: 'Lovely Kiss Smoothie',
                ingredients: {
                    Apple: 11,
                    Milk: 9,
                    Honey: 7,
                    Cacao: 8,
                },
            },
            {
                name: 'Lucky Chant Apple Pie',
                ingredients: {
                    Apple: 12,
                    Milk: 4,
                },
            },
            {
                name: 'Neroli\'s Restorative Tea',
                ingredients: {
                    Ginger: 11,
                    Apple: 15,
                    Mushroom: 9,
                },
            },
            {
                name: 'Sweet Scent Chocolate Cake',
                ingredients: {
                    Honey: 9,
                    Cacao: 8,
                    Milk: 7,
                },
            },
            {
                name: 'Warm Moomoo Milk',
                ingredients: {
                    Milk: 7,
                },
            },
            {
                name: 'Cloud Nine Soy Cake',
                ingredients: {
                    Egg: 8,
                    Soybeans: 7,
                },
            },
            {
                name: 'Hustle Protein Smoothie',
                ingredients: {
                    Soybeans: 15,
                    Cacao: 8,
                },
            },
            {
                name: 'Stalwart Vegetable Juice',
                ingredients: {
                    Tomato: 9,
                    Apple: 7,
                },
            },
            {
                name: 'Big Malasada',
                ingredients: {
                    Oil: 10,
                    Milk: 7,
                    Honey: 6,
                },
            },
            {
                name: 'Huge Power Soy Donuts',
                ingredients: {
                    Oil: 12,
                    Soybeans: 16,
                    Cacao: 7,
                },
            },
            {
                name: 'Explosion Popcorn',
                ingredients: {
                    Corn: 15,
                    Oil: 14,
                    Milk: 7,
                },
            },
            {
                name: 'Teatime Corn Scones',
                ingredients: {
                    Apple: 20,
                    Ginger: 20,
                    Corn: 18,
                    Milk: 9,
                },
            },
            {
                name: 'Petal Dance Chocolate Tart',
                ingredients: {
                    Apple: 11,
                    Cacao: 11,
                },
            },
            {
                name: 'Flower Gift Macarons',
                ingredients: {
                    Cacao: 25,
                    Egg: 25,
                    Honey: 17,
                    Milk: 10,
                },
            },
            {
                name: 'Early Bird Coffee Jelly',
                ingredients: {
                    Coffee: 16,
                    Milk: 14,
                    Honey: 12,
                },
            },
            {
                name: 'Zing Zap Spiced Cola',
                ingredients: {
                    Apple: 35,
                    Ginger: 20,
                    Leek: 20,
                    Coffee: 12,
                },
            },
        ],
    };
</script>
