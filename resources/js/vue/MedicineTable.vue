<template>
    <div class="container">
        <div class="spoiler">
            <div class="spoiler__header" @click="toggleSpoiler" data-group="1">
                Антипсихотики
            </div>
            <div class="spoiler__body" v-show="antipsyhotic_visible">

            </div>
        </div>
        <div class="spoiler">
            <div class="spoiler__header spoiler__closed" @click="toggleSpoiler" data-group="2">
                Антидепрессанты и противотревожные препараты
            </div>
            <div class="spoiler__body" v-show="antidepressants_visible">

            </div>
        </div>
        <div class="spoiler">
            <div class="spoiler__header spoiler__closed" @click="toggleSpoiler" data-group="7">
                Транквилизаторы, анксиолитики
            </div>
            <div class="spoiler__body" v-show="anxiolytics_visible">

            </div>
        </div>
        <div class="spoiler">
            <div class="spoiler__header spoiler__closed" @click="toggleSpoiler" data-group="5">
                Холинолитики, противопаркинсонические средства
            </div>
            <div class="spoiler__body" v-show="holinolytics_visible">

            </div>
        </div>
        <div class="spoiler">
            <div class="spoiler__header spoiler__closed" @click="toggleSpoiler" data-group="3">
                Нормотимики (стабилизаторы настроения)
            </div>
            <div class="spoiler__body" v-show="mood_stabilizers_visible">

            </div>
        </div>
        <div class="spoiler">
            <div class="spoiler__header spoiler__closed" @click="toggleSpoiler" data-group="10">
                Психостимуляторы
            </div>
            <div class="spoiler__body" v-show="psychodisleptic_visible">

            </div>
        </div>
        <div class="spoiler">
            <div class="spoiler__header spoiler__closed" @click="toggleSpoiler" data-group="8">
                Бета-адренолиноблокаторы
            </div>
            <div class="spoiler__body" v-show="beta_adrenoblockaters_visible">

            </div>
        </div>
        <div class="spoiler">
            <div class="spoiler__header spoiler__closed" @click="toggleSpoiler" data-group="9">
                Гипнотики (снотворные)
            </div>
            <div class="spoiler__body" v-show="hypnotics_visible">

            </div>
        </div>
        <div class="spoiler">
            <div class="spoiler__header spoiler__closed" @click="toggleSpoiler" data-group="4">
                Ингибиторы ацетилхолинэстеразы
            </div>
            <div class="spoiler__body" v-show="inhibitors_ache_visible">

            </div>
        </div>
    </div>
</template>

<script lang="ts">
    import { defineComponent } from 'vue';

        export default defineComponent({
            mount() {},
            setup() {},
            data() {
                return {
                    antipsyhotic_visible: false,
                    antidepressants_visible: false,
                    anxiolytics_visible: false,
                    holinolytics_visible: false,
                    mood_stabilizers_visible: false,
                    psychodisleptic_visible: false,
                    beta_adrenoblockaters_visible: false,
                    hypnotics_visible: false,
                    inhibitors_ache_visible: false
                }
            },
            methods: {
                async toggleSpoiler(event) {
                    function capitalizeFirstLetter(string) {
                        return string.charAt(0).toUpperCase() + string.slice(1);
                    }

                    const groupIdentificator = event.currentTarget.dataset.group;
                    const spoilerHeader = event.currentTarget;
                    const spoilerBody = spoilerHeader.parentElement.querySelector('div:last-child');

                    const response = await axios.get(`http://localhost:8080/api/medicines/${groupIdentificator}`, {
                        headers: { 'Accept': 'application/json' }
                    });

                    switch (groupIdentificator) {
                        case '1':
                            this.antipsyhotic_visible = !this.antipsyhotic_visible;
                            break;
                        case '2':
                            this.antidepressants_visible = !this.antidepressants_visible;
                            break;
                        case '7':
                            this.anxiolytics_visible = !this.anxiolytics_visible;
                            break;
                        case '5':
                            this.holinolytics_visible = !this.holinolytics_visible;
                            break;
                        case '3':
                            this.mood_stabilizers_visible = !this.mood_stabilizers_visible;
                            break;
                        case '10':
                            this.psychodisleptic_visible = !this.psychodisleptic_visible;
                            break;
                        case '8':
                            this.beta_adrenoblockaters_visible = !this.beta_adrenoblockaters_visible;
                            break;
                        case '9':
                            this.hypnotics_visible = !this.hypnotics_visible;
                            break;
                        case '4':
                            this.inhibitors_ache_visible = !this.inhibitors_ache_visible;
                            break;
                    }

                    let items = [];
                    let list = document.createElement('ul');
                    list.classList.add('spoiler__body-list');

                    if (response.data.data.length) {
                        response.data.data.forEach(function (el) {
                            let listItem = document.createElement('li');
                            let h4 = document.createElement('h4');
                            let generics = document.createElement('p');
                            let receptors = document.createElement('p');

                            generics.classList.add('line__low_case');
                            receptors.classList.add('line__low_case');

                            // Название препарата
                            h4.innerText = capitalizeFirstLetter(el.name);
                            h4.classList.add('pt-2');
                            // Дженерики
                            let genericsLabel = document.createElement('strong');
                            genericsLabel.innerText = 'Дженерики: ';
                            el.generics.forEach((e, i) => {
                                generics.innerText += e + (i < el.generics.length-1 ? ', ' : '');
                            })
                            generics.prepend(genericsLabel);

                            if (Array.isArray(el.receptors)) {
                                if (el.receptors.length > 0) {
                                    const transformedReceptors = el.receptors
                                        .map(receptor => {
                                            if (typeof receptor.name === 'string') { // Проверяем, что name - строка
                                                const [prefix, index] = receptor.name.split('-');
                                                return index ? `${prefix}<sub>${index}</sub>` : prefix;
                                            }
                                            return receptor.name; // Если нет "-", вернуть как есть
                                        })
                                        .join(', ');
                                    receptors.innerHTML = `<strong>Рецепторы: </strong> ${transformedReceptors}`;
                                } else {
                                    receptors.innerHTML = `<strong>Рецепторы: </strong> Не указаны`;
                                }
                            } else {
                                console.error("Ошибка: 'receptors' не является массивом для", el.name);
                                receptors.innerHTML = `<strong>Рецепторы: </strong> Не указаны`;
                            }

                            // Склейка
                            listItem.appendChild(h4);
                            listItem.appendChild(generics);
                            listItem.appendChild(receptors);
                            list.appendChild(listItem);
                            items.push(list);
                        });
                    } else {
                        let list = document.createElement('ul');
                        let theOnlyItem = document.createElement('li');
                        theOnlyItem.classList.add('text-center');
                        theOnlyItem.innerHTML = `<i class="bi bi-info-circle-fill"></i> Нет записей.`;
                        list.appendChild(theOnlyItem);
                        items.push(list);
                    }
                    spoilerBody.replaceChildren(...items);
                },
                open() {

                },
                close() {

                },
            }
    })
</script>

<style lang="sass" scoped>
.spoiler
    width: 100%
    margin-bottom: 15px

.spoiler__header
    background: #6fb6cc
    color: white
    padding: 15px
    cursor: pointer
    border-radius: 7px
    transition: background 0.3s ease

    &:hover
        background: #5a9bb8

.spoiler__body
    overflow: hidden
    min-height: 130px
    transition: max-height 0.3s ease, visibility 0.3s linear
    padding: 0
    border: 1px solid #ccc
    border-top: none
    border-bottom-left-radius: 7px
    border-bottom-right-radius: 7px

    ul.spoiler__body-list
        list-style: none
        margin: 0
        padding: 0 20px

        li
            padding: 15px 0

            h4
                font-size: 32px
                line-height: 35px

            p
                margin-bottom: 0
                font-size: 13px
                line-height: 16px
                font-weight: 500

            strong
                font-weight: 600
                letter-spacing: -1px

        li:not(:last-child)
            border-bottom: 1px solid #aaa

.spoiler__header.spoiler__open
    border-bottom-left-radius: 0
    border-bottom-right-radius: 0

.spoiler__header.spoiler__open + .spoiler__body
    max-height: 100%  // Присвойте максимальную высоту, например 1000px, если неизвестна точная высота содержимого
    visibility: visible
</style>
