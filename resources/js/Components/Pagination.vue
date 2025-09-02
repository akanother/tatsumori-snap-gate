<script>
import _ from 'lodash';

export default {
    name: 'Pagination',
    props: {
        data: {
            type: Object,
            required: true // paginate()で取得したデータ
        },
        theme: {
            type: String,
            default: 'primary',
        },
    },
    emits: ['update:modelValue', 'move-page'],
    computed: {
        hasPrev() {
            return this.data.prev_page_url !== null;
        },
        hasNext() {
            return this.data.next_page_url !== null;
        },
        pages() {
            const pages = [];
            for (let i = 1; i <= this.data.last_page; i++) {
                pages.push(i);
            }
            return pages;
        }
    },
    methods: {
        move(page) {
            if (!this.isCurrentPage(page)) {
                this.$emit('move-page', page);
            }
        },
        isCurrentPage(page) {
            return this.data.current_page === page;
        },
        getPageClass(page) {
            return {
                'page-item': true,
                'active': this.isCurrentPage(page)
            };
        },
        blurForcedly(e) {
            e.target.blur();
        }
    },
}
</script>

<template>
    <ul class="pagination pagination-circle">
        <li v-if="hasPrev">
            <a :class="`page-link bg-${theme}`" href="#" @click.prevent="move(data.current_page - 1)" @click="blurForcedly">&laquo;</a>
        </li>
        <li
            v-for="page in pages"
            :key="page"
            :class="getPageClass(page)"
        >
            <a :class="`page-link bg-${theme}`" href="#" @click.prevent="move(page)" @click="blurForcedly">{{ page }}</a>
        </li>
        <li class="page-item" v-if="hasNext">
            <a :class="`page-link bg-${theme}`" href="#" @click.prevent="move(data.current_page + 1)" @click="blurForcedly">&raquo;</a>
        </li>
    </ul>
</template>
