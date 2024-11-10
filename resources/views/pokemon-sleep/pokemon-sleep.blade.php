<x-layout title="Pokemon Sleep Recipes" remSize="20px">
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
                    <div class="flex flex-wrap rounded-b-box gap-2 py-4">
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
    @include('pokemon-sleep.recipes')
</x-layout>
