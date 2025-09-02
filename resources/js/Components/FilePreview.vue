/***

使用例：
1. HTML
    <FilePreview ref="filePreview" />
2. JavaScript
    this.$refs.filePreview.open(order);

***/

<template>
    <div class="modal fade" ref="modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-between align-items-center">
                    <h6 class="modal-title">注文書プレビュー</h6>
                    <button type="button" class="close text-md" data-dismiss="modal" aria-label="閉じる">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0" v-if="show && fileUrl">
                    <!-- <object> タグで PDF を表示 -->
                    <object id="pdf-object" :data="fileUrl" type="application/pdf" :width="width" :height="height">
                        <div class="text-muted p-3">PDF プレビューが利用できません。</div>
                    </object>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'FilePreview',
    props: {
        width: {
            type: String,
            default: '100%',
        },
        height: {
            type: String,
            default: '100%',
        },
    },
    data() {
        return {
            order: null,
            show: false,
        }
    },
    methods: {
        open(order) {
            this.order = order;
            this.show = true;
            this.$nextTick(() => {
                $(this.$refs.modal).modal('show');
            });
        },
    },
    computed: {
        fileUrl() {
            const filePath = this.order?.file_path || '';
            if(filePath) {
                const baseUrl = route('order.file.preview');
                return `${baseUrl}/${filePath}?t=${Date.now()}`;
            }
            return '';
        }
    },
    mounted() {
        $(this.$refs.modal).on('hidden.bs.modal', () => {
            this.show = false; // DOMを消すために必要
        });
    },
}
</script>

<style scoped>
#pdf-object {
    display: block;
}
</style>
