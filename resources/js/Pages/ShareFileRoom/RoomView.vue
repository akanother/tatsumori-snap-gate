<!--<template>-->
<!--    <div class="container-fluid p-0">-->
<!--        &lt;!&ndash; ヘッダー &ndash;&gt;-->
<!--        <header class="navbar navbar-expand-lg navbar-dark bg-gradient-primary shadow-sm px-3">-->
<!--            <div class="container-fluid">-->
<!--                &lt;!&ndash; ロゴ or ブランド名 &ndash;&gt;-->
<!--                <span class="navbar-brand fw-bold d-flex align-items-center">-->
<!--                    <i class="fas fa-cloud me-4"></i>-->
<!--                    <span v-if="room.target_scope === 'internal'">SnapGate File Share Room</span>-->
<!--                    <span v-else>株式会社 龍森 ファイルダウンロード</span>-->
<!--                </span>-->
<!--            </div>-->
<!--        </header>-->

<!--        &lt;!&ndash; ROOMタイトル表示 &ndash;&gt;-->
<!--        <div class="bg-light py-3 text-center border-bottom">-->
<!--            <h2 class="h4 mb-0">{{ room.name }}</h2>-->
<!--        </div>-->

<!--        &lt;!&ndash; コンテンツ本体 &ndash;&gt;-->
<!--        <div class="container my-4">-->
<!--            &lt;!&ndash; 社外ROOM: パスワード認証前 &ndash;&gt;-->
<!--            <div v-if="room.requires_password && !isAuthenticated" class="text-center">-->
<!--                <div class="card mx-auto" style="max-width: 320px;">-->
<!--                    <p class="text-left">本人確認のため、通知した認証コードを入力してください。このコードは弊社担当者により、送信しております。</p>-->
<!--                    <div class="card-body">-->
<!--                        <h5 class="card-title mb-3">-->
<!--                            <i class="fas fa-lock me-2"></i> 認証コード-->
<!--                        </h5>-->

<!--                        <input-->
<!--                            v-model="password"-->
<!--                            type="password"-->
<!--                            class="form-control mb-3"-->
<!--                            placeholder="パスワードを入力"-->
<!--                            @keyup.enter="submitPassword"-->
<!--                        >-->
<!--                        <button class="btn btn-primary w-100" @click="submitPassword">-->
<!--                            認証する-->
<!--                        </button>-->
<!--                        <div v-if="errorMessage" class="mt-3 text-danger small">-->
<!--                            {{ errorMessage }}-->
<!--                        </div>-->
<!--                    </div>-->
<!--                </div>-->
<!--            </div>-->

<!--            &lt;!&ndash; ファイル一覧 &ndash;&gt;-->
<!--            <div v-else>-->
<!--                <div class="d-flex justify-content-end mb-3">-->
<!--                    <form :action="`/r/zip/${token}`" method="POST">-->
<!--                        <input type="hidden" name="_token" :value="csrf">-->
<!--                        <button class="btn btn-success">-->
<!--                            <i class="fas fa-file-archive me-2"></i> 全ファイルをZIPでダウンロード-->
<!--                        </button>-->
<!--                    </form>-->
<!--                </div>-->

<!--                <div v-if="files.length === 0" class="text-center text-muted">-->
<!--                    ファイルがまだ登録されていません-->
<!--                </div>-->
<!--                <div v-else class="table-responsive">-->
<!--                    <table class="table table-striped">-->
<!--                        <thead class="table-light">-->
<!--                        <tr>-->
<!--                            <th>ファイル名</th>-->
<!--                            <th class="text-end">サイズ</th>-->
<!--                            <th class="text-center">アップロード日時</th>-->
<!--                            <th class="text-center">操作</th>-->
<!--                        </tr>-->
<!--                        </thead>-->
<!--                        <tbody>-->
<!--                        <tr v-for="file in files" :key="file.id">-->
<!--                            <td>{{ file.name }}</td>-->
<!--                            <td class="text-end">{{ formatSize(file.size) }}</td>-->
<!--                            <td class="text-center">{{ file.uploaded_at }}</td>-->
<!--                            <td class="text-center">-->
<!--                                <form :action="`/r/f/${file.id}`" method="POST" style="display:inline;">-->
<!--                                    <input type="hidden" name="_token" :value="csrf">-->
<!--                                    <input type="hidden" name="path" :value="file.path">-->
<!--                                    <button class="btn btn-sm btn-outline-primary">-->
<!--                                        <i class="fas fa-download"></i> ダウンロード-->
<!--                                    </button>-->
<!--                                </form>-->
<!--                            </td>-->
<!--                        </tr>-->
<!--                        </tbody>-->
<!--                    </table>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->
<!--    </div>-->
<!--</template>-->
<template>
    <div class="container-fluid p-0">
        <!-- =========================
            ヘッダー
        ========================== -->
        <header
            v-if="room.target_scope === 'internal'"
            class="navbar navbar-expand-lg navbar-dark bg-gradient-primary shadow-sm px-3">
            <div class="container-fluid">
                <span class="navbar-brand fw-bold d-flex align-items-center">
                    <i class="fas fa-cloud me-3"></i>
                    SnapGate File Share Room
                </span>
            </div>
        </header>

        <header
            v-else
            class="navbar navbar-dark bg-external-header shadow-sm justify-content-center">
            <span class="navbar-brand fw-bold">
                株式会社 龍森　ファイルダウンロード
            </span>
        </header>

        <!-- =========================
            ROOMタイトル
        ========================== -->
        <div class="bg-light py-3 text-center border-bottom">
            <h2 class="h4 mb-0">{{ room.name }}</h2>
        </div>

        <!-- =========================
            コンテンツ本体
        ========================== -->
        <div class="container my-4">

            <!-- ======== 社外ROOM: パスワード認証 ======== -->
            <div v-if="room.requires_password && !isAuthenticated" class="text-center">
                <div
                    class="card mx-auto"
                    :class="room.target_scope === 'external' ? 'external-auth-card' : ''"
                    style="max-width: 400px;">

                    <!-- 社外向けは公式感を強調 -->
                    <div v-if="room.target_scope === 'external'" class="card-header bg-light text-center fw-bold">
                        セキュア認証
                    </div>

                    <div class="card-body">
                        <p class="small text-muted text-start mb-3">
                            通知された認証コードを入力してください。<br>
                            このコードは弊社担当者より安全に送信されています。
                        </p>
                        <input
                            v-model="password"
                            type="password"
                            class="form-control form-control-lg mb-3"
                            placeholder="認証コードを入力"
                            @keyup.enter="submitPassword"
                        >
                        <button class="btn btn-primary w-100 fw-bold" @click="submitPassword">
                            認証する
                        </button>
                        <div v-if="errorMessage" class="mt-3 text-danger small">
                            {{ errorMessage }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- ======== ファイル一覧 ======== -->
            <div v-else>
                <div class="d-flex justify-content-end mb-3">
                    <form :action="`/r/zip/${token}`" method="POST">
                        <input type="hidden" name="_token" :value="csrf">
                        <button class="btn btn-success">
                            <i class="fas fa-file-archive me-2"></i> 全ファイルをZIPでダウンロード
                        </button>
                    </form>
                </div>

                <!-- ファイルなし -->
                <div v-if="files.length === 0" class="text-center text-muted">
                    ファイルがまだ登録されていません
                </div>

                <!-- ファイル一覧テーブル -->
                <div v-else class="table-responsive">
                    <table class="table table-striped file-table">
                        <thead>
                        <tr>
                            <th>ファイル名</th>
                            <th class="text-end">サイズ</th>
                            <th class="text-center">アップロード日時</th>
                            <th class="text-center">操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="file in files" :key="file.id">
                            <td>{{ file.name }}</td>
                            <td class="text-end">{{ formatSize(file.size) }}</td>
                            <td class="text-center">{{ file.uploaded_at }}</td>
                            <td class="text-center">
                                <form :action="`/r/f/${file.id}`" method="POST" style="display:inline;">
                                    <input type="hidden" name="_token" :value="csrf">
                                    <input type="hidden" name="path" :value="file.path">
                                    <button class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-download"></i> ダウンロード
                                    </button>
                                </form>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>


            <!-- ファイル一覧の下部 -->
            <div class="mt-4">
                <!-- 社内ROOM：日本語固定 -->
                <div v-if="room.target_scope === 'internal'" class="alert alert-info border internal-notice">
                    <h6 class="fw-bold mb-2">
                        <i class="fas fa-info-circle me-2"></i>
                        注意事項
                    </h6>
                    <ul class="mb-0 small">
                        <li>このシステムは社内利用専用です。第三者へのURL共有は禁止されています。</li>
                        <li>ダウンロードしたファイルは社内業務以外での使用を禁止します。</li>
                        <li>アクセス権限がない場合はシステム管理者に連絡してください。</li>
                    </ul>
                </div>

                <!-- 社外ROOM：ブラウザ言語判定で切り替え -->
                <div v-else class="alert alert-light border external-notice">
                    <h6 class="fw-bold mb-2">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        {{ userLang === 'ja' ? '注意事項' : 'Notice' }}
                    </h6>
                    <ul class="mb-0 small">
                        <li v-for="(text, index) in noticeTexts[userLang] || noticeTexts['ja']" :key="index">
                            {{ text }}
                        </li>
                    </ul>
                </div>
            </div>


        </div>



    </div>
</template>


<script>
import axios from "axios";

export default {
    name: "ShareFileRoomPage",
    props: {
        token: {
            type: String,
            required: true,
        },
        room: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            password: "",
            errorMessage: "",
            isAuthenticated: false, // 社外ROOMで認証成功後にtrue
            files: [],
            csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            userLang: "ja", // デフォルトは日本語
            noticeTexts: {
                ja: [
                    "このページは当社から通知されたお客様専用です。他者へのURL共有は禁止されています。",
                    "ダウンロードしたファイルは通知された用途以外での使用を禁止します。",
                    "有効期限を過ぎるとアクセスできなくなります。期限内にダウンロードしてください。",
                    "ご不明点がございましたら、担当営業または弊社窓口までご連絡ください。"
                ],
                en: [
                    "This page is exclusively for customers notified by our company. Sharing the URL with others is strictly prohibited.",
                    "The downloaded files must not be used for any purpose other than the intended one.",
                    "After the expiration date, access to the files will no longer be available. Please download them within the period.",
                    "If you have any questions, please contact your sales representative or our support desk."
                ],
                zh: [
                    "此页面仅供我司通知的客户使用，禁止与他人共享此URL。",
                    "下载的文件不得用于通知内容以外的用途。",
                    "过期后将无法访问，请在有效期内下载文件。",
                    "如有疑问，请联系负责的销售人员或我司客服。"
                ],
                ko: [
                    "이 페이지는 당사에서 통지한 고객 전용입니다. 다른 사람과의 URL 공유는 금지됩니다.",
                    "다운로드한 파일은 통지된 용도 이외의 사용을 금지합니다.",
                    "유효 기간이 지나면 접근할 수 없으니 기간 내에 다운로드해 주십시오.",
                    "문의사항이 있으시면 담당 영업 또는 당사 창구로 연락해 주십시오."
                ]
            }
        };
    },
    mounted() {
        // 社内ROOMは即ファイル一覧取得
        if (!this.room.requires_password) {
            this.fetchFiles();
        }

        // ブラウザ言語コードを取得（例: "en-US", "ja", "zh-CN"）
        const browserLang = navigator.language || navigator.userLanguage;

        // 言語コードを2文字に統一
        const langCode = browserLang.split('-')[0]; // "en-US" → "en"

        // Vueデータに保存
        this.userLang = langCode;

        // ★テスト用: 強制的に英語に変更
        // this.userLang = "en";  // 英語
        // this.userLang = "zh";  // 中国語
        // this.userLang = "ko";  // 韓国語

    },
    methods: {
        /**
         * パスワード認証
         */
        async submitPassword() {
            if (!this.password) {
                this.errorMessage = "パスワードを入力してください";
                return;
            }
            this.errorMessage = "";

            try {
                const res = await axios.post("/r/verify", {
                    token: this.token,
                    password: this.password,
                });

                if (res.data.status === "ok") {
                    this.isAuthenticated = true;
                    this.fetchFiles(); // 認証成功後にファイル一覧取得
                } else {
                    this.errorMessage = res.data.message || "認証に失敗しました";
                }
            } catch (error) {
                console.error(error);
                this.errorMessage = "システムエラーが発生しました";
            }
        },

        /**
         * ファイル一覧取得
         */
        async fetchFiles() {
            try {
                const res = await axios.get(`/r/files/${this.token}`);
                this.files = res.data.files || [];
            } catch (error) {
                console.error("ファイル一覧取得エラー", error);
                this.errorMessage = "ファイル一覧が取得できませんでした";
            }
        },

        /**
         * サイズ表示整形
         */
        formatSize(size) {
            if (size < 1024) return size + " B";
            if (size < 1024 * 1024) return (size / 1024).toFixed(1) + " KB";
            return (size / (1024 * 1024)).toFixed(1) + " MB";
        },
    },
};
</script>

<style scoped>
/* ブランド用グラデーション */
.bg-gradient-primary {
    background: linear-gradient(90deg, #4e73df 0%, #1cc88a 100%);
}

/* =========================
   社内ROOM用デザイン
========================= */
.bg-gradient-primary {
    background: linear-gradient(90deg, #4e73df 0%, #1cc88a 100%);
}

/* =========================
   社外ROOM用デザイン
========================= */
.bg-external-header {
    background-color: #002b45; /* 濃紺ベースで信頼感を */
}

.external-auth-card {
    border: 1px solid #dee2e6;
    border-radius: 4px;
    background-color: #fff;
}

/* =========================
   ファイル一覧
========================= */
.file-table thead {
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
}
.file-table td,
.file-table th {
    vertical-align: middle;
}

/* =========================
   社外ROOM: ボタン強調
========================= */
.external-auth-card .btn-primary {
    background-color: #003366;
    border-color: #003366;
}
.external-auth-card .btn-primary:hover {
    background-color: #002244;
    border-color: #002244;
}

/* =========================
   テキストスタイル
========================= */
.text-muted {
    font-size: 0.9rem;
}

.external-notice {
    background-color: #fdfdfd;
    font-size: 0.85rem;
    line-height: 1.6;
}
.external-notice h6 {
    font-size: 1rem;
    color: #333;
}
.external-notice ul {
    padding-left: 1.2rem;
}
.external-notice li {
    margin-bottom: 0.25rem;
}

</style>
