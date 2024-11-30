<template>
    <div>
        <h3>Цены на услуги</h3>
        <div v-for="(row, index) in rows" :key="index" class="row mb-3">
            <div class="col-md-2">
                <input
                    class="form-control"
                    v-model="row.group_name"
                    :name="`group_name[${index}]`"
                    placeholder="Имя группы цен"
                />
            </div>
            <div class="col-md-3">
                <input
                    class="form-control"
                    v-model="row.name"
                    :name="`name[${index}]`"
                    placeholder="Название услуги"
                />
            </div>
            <div class="col-md-3">
                <input
                    class="form-control"
                    v-model="row.price"
                    :name="`price[${index}]`"
                    placeholder="Цена за услугу"
                    type="number"
                />
            </div>
            <div class="col-md-2">
                <input
                    class="form-control"
                    v-model="row.discount_price"
                    :name="`discount_price[${index}]`"
                    placeholder="Скидка"
                    type="number"
                />
            </div>
            <div class="col-md-2">
                <div class="btn-group">
                    <button class="btn btn-primary" type="button" @click="addLine">Добавить строку</button>
                    <button
                        v-if="index > 0"
                        class="btn btn-danger"
                        type="button"
                        @click="removeLine(index)"
                    >
                        Удалить строку
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'DoctorPricelist',
    props: {
        initialRows: {
            type: Array,
            default: () => []
        }
    },
    data() {
        return {
            rows: []
        };
    },
    mounted() {
        this.rows = this.initializeRows(this.initialRows);
    },
    methods: {
        initializeRows(data) {
            if (data.length === 0) {
                return [{ group_name: '', name: '', price: 0, discount_price: 0 }];
            }
            return data;
        },
        addLine() {
            this.rows = [...this.rows, { group_name: '', name: '', price: 0, discount_price: 0 }];
        },
        removeLine(index) {
            if (this.rows.length > 1) {
                this.rows = this.rows.filter((_, i) => i !== index);
            }
        }
    }
};
</script>

<style scoped>
.row {
    align-items: center;
}
</style>
