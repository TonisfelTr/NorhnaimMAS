<template>
    <div class="mb-3">
        <div v-for="(form, index) in forms" :key="index" class="row mb-3 row-cols-5 border-0">
            <div class="col-md-2">
                <select name="forms[]" class="form-control" v-model="form.type" @change="updateForms(index, $event)" required>
                    <option value="ampules">Ампулы</option>
                    <option value="tablets">Таблетки</option>
                    <option value="dragees">Драже</option>
                    <option value="capsules">Капсулы</option>
                </select>
            </div>
            <div class="col-md-2" v-if="form.type === 'ampules'">
                <input type="number" step="0.1" name="concentration[]" class="form-control" placeholder="Концентрация" v-model="form.concentration" required>
            </div>
            <input type="hidden" name="concentration[]" v-if="form.type !== 'ampules'" :value="form.concentration">
            <div class="col-md-2">
                <input type="number" step="0.5" name="dose[]" class="form-control" placeholder="Доза в единице" v-model="form.dose" required>
            </div>
            <div class="col-md-3">
                <input type="number" name="count[]" class="form-control" placeholder="Кол-во единиц" v-model="form.count" required>
            </div>
            <div class="col-md-2 btn-group">
                <button class="btn btn-primary" type="button" @click="addLine">Добавить</button>
                <button class="btn btn-danger" type="button" @click="removeLine(index)" v-if="forms.length > 1">Удалить</button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'DrugForm',
    props: {
        initialForms: {
            type: String,
            default: '{}'
        }
    },
    data() {
        return {
            forms: []
        };
    },
    mounted() {
        const parsedForms = JSON.parse(this.initialForms);
        this.forms = this.parseForms(parsedForms);
    },
    methods: {
        parseForms(data) {
            const formsArray = [];

            Object.keys(data).forEach(type => {
                const doses = data[type];

                Object.keys(doses).forEach(dose => {
                    const volumes = doses[dose];

                    if (type === 'ampules') {
                        // Для ампул обрабатываем концентрацию, объём и количество
                        Object.keys(volumes).forEach(volume => {
                            formsArray.push({
                                type,
                                concentration: parseFloat(dose), // Концентрация
                                dose: parseFloat(volume),        // Объём ампулы
                                count: volumes[volume]           // Количество ампул
                            });
                        });
                    } else {
                        // Для остальных типов формы
                        const counts = doses[dose];
                        if (Array.isArray(counts)) {
                            counts.forEach(count => {
                                formsArray.push({
                                    type,
                                    concentration: '',
                                    dose: parseFloat(dose),
                                    count
                                });
                            });
                        } else {
                            formsArray.push({
                                type,
                                concentration: '',
                                dose: parseFloat(dose),
                                count: counts
                            });
                        }
                    }
                });
            });

            return formsArray;
        },
        addLine() {
            this.forms.push({ type: '', concentration: '', dose: '', count: '' });
        },
        removeLine(index) {
            if (this.forms.length > 1) {
                this.forms.splice(index, 1);
            }
        },
        updateForms(index, event) {
            this.forms[index].type = event.target.value;
        }
    }
}
</script>

<style scoped lang="scss">
.row {
    border: 1px solid #ccc;
    padding: 10px;
    margin-bottom: 10px;
}
</style>
