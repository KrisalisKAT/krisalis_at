<x-layout title="Pokemon Sleep Recipes">
    <div class="mx-10 my-4 p-4 gap-y-4 text-lg">
        <div class="card card-normal bg-base-300">
            <div class="card-body">
                <h1 class="card-title">Pokemon Sleep Recipes</h1>
                <p class="mb-6">For checking what recipes you may not have unlocked yet but could make with ingredients on-hand.</p>
                <div x-data="appData" class="flex flex-col gap-y-6">
                    <div class="flex flex-wrap gap-x-10 gap-y-6">
                        <div class="flex flex-col">
                            <label for="potSize">
                                Base Pot&nbsp;Size
                            </label>
                            <input id="potSize" type="number" step="1" min="15" max="200"
                                   class="input input-bordered input-sm w-28"
                                   x-model.number="potSize"/>
                        </div>
                        <div class="flex flex-col">
                            <label for="tempPotSize">
                                Temporary Pot&nbsp;Size
                            </label>
                            <div>
                                <input id="tempPotSize" type="number" step="1" x-bind:min="potSize" max="200"
                                       class="input input-bordered input-sm w-28" x-model.number="tempPotSize"/>
                                <button class="btn btn-outline btn-accent btn-sm" x-on:click="tempPotIncrease = 0">
                                    Reset
                                </button>
                            </div>
                        </div>
                        <label class="cursor-pointer flex flex-col">
                            <span>Hide unavailable recipes</span>
                            <input type="checkbox" class="toggle" x-model="hideUnavailable"/>
                        </label>
                    </div>
                    <div class="grid grid-flow-dense rounded-b-box gap-2 py-4"
                         style="grid-template-columns: repeat(auto-fill, minmax(200px, 1fr))">
                        <template x-for="(quantity, ingredient) in ingredientQuantities">
                            <div class="bg-base-100 rounded-box flex flex-col py-2 px-4 gap-y-2">
                                <label x-bind:for="'ing_'+ingredient" x-text="ingredients[ingredient]"></label>
                                <div class="flex gap-x-2">
                                <input x-bind:id="'ing_'+ingredient" type="number" step="1" min="0"
                                       class="input input-bordered input-sm w-20"
                                       x-model.number="ingredientQuantities[ingredient]"/>
                                    <button class="btn btn-sm btn-primary" x-on:click="ingredientQuantities[ingredient]++">+1</button>
                                    <button class="btn btn-sm btn-primary" x-on:click="ingredientQuantities[ingredient] += 5">+5</button>
                                </div>
                            </div>
                        </template>
                    </div>
                    <div>
                        <div role="tablist" class="tabs tabs-lifted">
                            <template x-for="recipeGroup in recipeGroups">
                                <a role="tab"
                                   class="tab text-xl p-2"
                                   x-bind:class="{ 'tab-active': tab === recipeGroup }"
                                   x-on:click="tab = recipeGroup"
                                   x-text="recipeGroup"></a>
                            </template>
                        </div>
                        <div class="grid grid-flow-dense bg-base-100 rounded-b-box gap-2 p-4"
                             style="grid-template-columns: repeat(auto-fill, minmax(200px, 1fr))"
                             x-bind:class="{
                        'rounded-tl-box': tab !== recipeGroups[0],
                        'rounded-tr-box': tab !== recipeGroups.slice(-1)[0]
                    }">
                            <template x-for="recipe in sortedRecipes(tab)">
                                <div class="card card-bordered border-primary"
                                     x-show="canMake(recipe) || !hideUnavailable"
                                     x-bind:class="{ 'opacity-20': !canMake(recipe) && !hideUnavailable }">
                                    <div class="card-body">
                                        <h2 class="card-title text-2xl" x-text="recipe.name"></h2>
                                        <ul>
                                            <template x-for="(quantity, item) in recipe.ingredients">
                                                <li><span x-text="quantity"></span> <span
                                                        x-text="ingredients[item]"></span>
                                                </li>
                                            </template>
                                            <li x-show="recipe.catchAll">Any</li>
                                        </ul>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        const ingredients = {
            Leek: 'Large Leek',
            Mushroom: 'Tasty Mushroom',
            Egg: 'Fancy Egg',
            Potato: 'Soft Potato',
            Apple: 'Fancy Apple',
            Herb: 'Fiery Herb',
            Sausage: 'Bean Sausage',
            Milk: 'Moomoo Milk',
            Honey: 'Honey',
            Oil: 'Pure Oil',
            Ginger: 'Warming Ginger',
            Tomato: 'Snoozy Tomato',
            Cacao: 'Soothing Cacao',
            Tail: 'Slowpoke Tail',
            Soybeans: 'Greengrass Soybeans',
            Corn: 'Greengrass Corn',
            Coffee: 'Rousing Coffee',
        }
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
                // {
                //     name: '',
                //     ingredients: {},
                // },
            ],
        }
        document.addEventListener('alpine:init', () => {
            Alpine.data('appData', () => ({
                _potSize: Number(window.localStorage.getItem('potSize')) || 15,
                get potSize() {
                    return this._potSize;
                },
                set potSize(value) {
                    this._potSize = value;
                    window.localStorage.setItem('potSize', value);
                },
                tempPotIncrease: 0,
                get tempPotSize() {
                    return this._potSize + this.tempPotIncrease;
                },
                set tempPotSize(value) {
                    this.tempPotIncrease = value - this._potSize;
                },
                hideUnavailable: false,
                _tab: window.localStorage.getItem('recipeTab') ?? 'Salad',
                get tab() {
                    return this._tab;
                },
                set tab(value) {
                    this._tab = value;
                    window.localStorage.setItem('recipeTab', value);
                },
                recipeGroups: Object.keys(recipes),
                ingredientQuantities: Object.fromEntries(Object.keys(ingredients).map(ing => ([ing, 0]))),
                ingredientsTotal(recipe) {
                    return Object.values(recipe.ingredients).reduce((sum, q) => sum + q, 0);
                },
                canMake(recipe) {
                    return this.ingredientsTotal(recipe) <= this.potSize && !Object.entries(recipe.ingredients)
                        .some(([ingredient, quantity]) => this.ingredientQuantities[ingredient] < quantity)
                },
                sortedRecipes(tab) {
                    return recipes[tab].toSorted((recipeA, recipeB) => {
                        const canMakeA = this.canMake(recipeA);
                        const canMakeB = this.canMake(recipeB);
                        if (canMakeA !== canMakeB) {
                            return canMakeA ? -1 : 1;
                        }
                        return this.ingredientsTotal(recipeB) - this.ingredientsTotal(recipeA)
                    })
                }
            }))
        })
    </script>
</x-layout>
