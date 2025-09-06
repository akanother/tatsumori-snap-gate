<template>
    <div class="bg-gray-50 min-h-screen d-flex flex-column">
        <header class="p-3 bg-white shadow-sm d-flex align-items-center justify-content-between sticky-top">
            <div class="d-flex align-items-center">
                <i class="fas fa-camera-retro me-2"></i>
                <strong>SNAP GATE</strong>
            </div>
            <small class="text-muted">Room: {{ roomCode || '-' }}</small>
        </header>

        <main class="container py-3 with-footer" style="max-width: 640px;">
            <div v-if="expiresAt" class="alert alert-info py-2 px-3 mb-3">
                <i class="far fa-clock me-2"></i>
                この招待は <strong>{{ humanExp }}</strong> まで有効です
            </div>

            <div class="card mb-3">
                <div class="card-body py-3">
                    <p class="mb-2 small text-muted">カメラで撮影するか、端末の写真から選択してください。</p>
                    <ul class="small mb-0 text-muted ps-3">
                        <li>最大 {{ maxFiles }} 枚まで</li>
                        <li>1枚あたり {{ maxMB }} MB まで</li>
                    </ul>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body d-grid gap-2">
                    <button type="button" class="btn btn-outline-primary btn-lg py-3 w-100 mb-2"
                            @click="openCamera" :disabled="uploading">
                        <i class="fas fa-video me-2"></i> ライブカメラで撮影
                    </button>

                    <label class="btn btn-primary btn-lg py-3 w-100 mb-0">
                        <i class="fas fa-camera me-2"></i> 写真を選択（端末）
                        <input ref="fileInput" type="file" class="d-none"
                               accept="image/*" capture="environment" multiple @change="onPick"/>
                    </label>
                </div>
            </div>

            <div class="card mb-3" v-if="files.length">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>{{ files.length }}</strong> / {{ maxFiles }} 枚
                            <span class="text-muted small ms-2">
                合計 {{ (totalBytes / 1024 / 1024).toFixed(2) }} MB
              </span>
                        </div>
                        <div>
              <span class="badge" :class="okToUpload ? 'bg-success' : 'bg-danger'">
                {{ okToUpload ? 'OK' : '上限超過あり' }}
              </span>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="files.length" class="mb-3">
                <div class="row g-2">
                    <div v-for="(item, idx) in files" :key="item.id" class="col-4">
                        <div class="thumb border rounded overflow-hidden">
                            <img v-if="item.previewable" class="w-100 thumb-img" :src="item.url" :alt="item.file.name"/>
                            <div v-else class="thumb-fallback">
                                <i class="far fa-file-image fa-2x text-muted"></i>
                            </div>

                            <span class="badge position-absolute top-0 start-0 m-1"
                                  :class="item.tooBig ? 'bg-danger' : 'bg-dark'" style="z-index:4">
                {{ (item.file.size / 1024 / 1024).toFixed(1) }}MB
              </span>

                            <button type="button" class="remove-btn" @click.stop.prevent="removeAt(idx)"
                                    :disabled="uploading" :aria-label="`remove ${item.file.name}`" title="削除">
                                <i class="fas fa-times" aria-hidden="true"></i>
                                <span class="sr-only">×</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="uploading" class="mb-3">
                <div class="progress" style="height: 10px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                         :style="{ width: progress + '%' }" :aria-valuenow="progress" aria-valuemin="0"
                         aria-valuemax="100"></div>
                </div>
                <div class="d-flex justify-content-between mt-2 small text-muted">
                    <span>アップロード中…</span><span>{{ progress }}%</span>
                </div>
            </div>

            <div v-if="result" class="card mb-5">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <strong class="text-success">アップロード完了</strong>
                    </div>
                    <div class="small text-muted">保存数: {{ result.count }} 点</div>
                </div>
            </div>
        </main>

        <footer class="action-footer bg-white border-top p-2">
            <div class="btn-group w-100 equal-group" role="group" aria-label="actions">
                <button type="button" class="btn btn-outline-warning" @click="clearAll"
                        :disabled="files.length === 0 || uploading">
                    <i class="fas fa-trash-alt me-1"></i> 全てクリア
                </button>
                <button type="button" class="btn btn-success" :disabled="!okToUpload || uploading || files.length === 0"
                        @click="upload">
                    <i class="fas fa-cloud-upload-alt me-1"></i> アップロード
                </button>
                <button type="button" class="btn btn-outline-danger" @click="onLogoutClick" :disabled="uploading">
                    <i class="fas fa-sign-out-alt me-1"></i> 退出
                </button>
            </div>
        </footer>

        <form ref="logoutForm" :action="logoutUrl" method="post" class="d-none">
            <input type="hidden" name="_token" :value="csrfToken"/>
        </form>

        <!-- ==== カメラオーバーレイ ==== -->
        <div v-if="camera.open" class="cam-overlay">
            <div class="cam-toolbar">
                <button type="button" class="btn btn-light btn-sm" @click="toggleFacing" :disabled="camera.busy">
                    <i class="fas fa-sync-alt me-1"></i> 切替
                </button>
                <button type="button" class="btn btn-outline-light btn-sm" @click="closeCamera" :disabled="camera.busy">
                    <i class="fas fa-times me-1"></i> 閉じる
                </button>
            </div>

            <video ref="videoRef" class="cam-video" autoplay playsinline muted></video>

            <div class="cam-bottom">
                <button type="button" class="btn btn-primary btn-lg cam-shutter"
                        :disabled="camera.busy || ui.shutterLock"
                        @click="shoot">
                    <i class="fas fa-dot-circle me-2"></i> 撮影
                </button>
            </div>

            <!-- フラッシュ（白フェード） -->
            <div class="flash" :class="{ active: ui.flash }"></div>
            <!-- 撮影マーク（中央） -->
            <div class="shot-mark" v-if="ui.shotMark">
                <i class="fas fa-check-circle me-2"></i> 撮影しました
            </div>
            <!-- アクセシビリティ：ライブリージョン -->
            <div class="sr-only" aria-live="polite" ref="liveRegion"></div>
        </div>
    </div>
</template>

<script>
import { Head } from '@inertiajs/vue3'
import Swal from 'sweetalert2'
import 'sweetalert2/dist/sweetalert2.min.css'

export default {
    name: 'Capture',
    components: { Head },
    props: {
        roomCode: { type: String, default: '' },
        maxFiles: { type: Number, default: 10 },
        maxMB: { type: Number, default: 20 },
        uploadUrl: { type: String, required: true },
        logoutUrl: { type: String, required: true },
        expiresAt: { type: String, default: null },
        csrfToken: { type: String, required: true },
    },
    data() {
        return {
            files: [],
            uploading: false,
            progress: 0,
            result: null,
            camera: { open: false, facing: 'environment', busy: false, stream: null },
            pingTimer: null,
            // ▼ 追加：UI & Audio 状態
            ui: { flash: false, shotMark: false, shutterLock: false },
            audio: { ctx: null, unlocked: false },
        }
    },
    computed: {
        maxBytes() {
            return this.maxMB * 1024 * 1024
        },
        totalBytes() {
            return this.files.reduce((a, b) => a + (b.file?.size || 0), 0)
        },
        okToUpload() {
            if (this.files.length === 0) return false
            if (this.files.length > this.maxFiles) return false
            if (this.files.some(f => f.tooBig)) return false
            return true
        },
        humanExp() {
            if (!this.expiresAt) return ''
            try {
                const d = new Date(this.expiresAt)
                const y = d.getFullYear(), m = String(d.getMonth() + 1).padStart(2, '0'),
                    day = String(d.getDate()).padStart(2, '0')
                const hh = String(d.getHours()).padStart(2, '0'), mm = String(d.getMinutes()).padStart(2, '0')
                return `${y}/${m}/${day} ${hh}:${mm}`
            } catch {
                return this.expiresAt
            }
        },
    },
    mounted() {
        // keep-alive ping（セッション延命）
        this.pingTimer = setInterval(async () => {
            try {
                await fetch('/gate/ping', { method: 'GET', credentials: 'same-origin', cache: 'no-store' })
            } catch (_) {}
        }, 60000)
    },
    beforeUnmount() {
        if (this.pingTimer) clearInterval(this.pingTimer)
        this.files.forEach(f => f.url && URL.revokeObjectURL(f.url))
        this.closeCamera()
        // AudioContext のクリーンアップ（任意）
        try { this.audio?.ctx?.close?.() } catch {}
    },
    methods: {
        async onPick(e) {
            const picked = Array.from(e.target.files || [])
            if (!picked.length) return
            const allowance = Math.max(this.maxFiles - this.files.length, 0)
            const use = picked.slice(0, allowance)
            for (const file of use) {
                const blob = await this.maybeCompress(file)
                this.pushFile(blob, file.name)
            }
            this.$refs.fileInput.value = ''
            this.toast(`追加: ${use.length} 枚`)
        },
        pushFile(blob, originalName) {
            const name = this.safeName(originalName, 'jpg')
            const file = new File([blob], name, { type: blob.type || 'image/jpeg' })
            const previewable = /image\/(jpeg|png|webp)/.test(file.type)
            const tooBig = file.size > this.maxBytes
            this.files.push({
                id: `${Date.now()}-${Math.random().toString(36).slice(2, 8)}`,
                file, url: previewable ? URL.createObjectURL(file) : '',
                previewable, tooBig,
            })
        },
        removeAt(i) {
            const f = this.files[i]
            if (f?.url) URL.revokeObjectURL(f.url)
            this.files.splice(i, 1)
        },
        clearAll() {
            this.files.forEach(f => f.url && URL.revokeObjectURL(f.url))
            this.files = []
            this.result = null
            this.progress = 0
        },

        async upload() {
            if (!this.okToUpload || this.uploading) return
            this.uploading = true
            this.progress = 0
            this.result = null

            const fd = new FormData()
            this.files.forEach(f => fd.append('images[]', f.file))
            fd.append('_token', this.csrfToken)

            try {
                const res = await this.xhrPost(this.uploadUrl, fd, e => {
                    if (e.lengthComputable) this.progress = Math.min(100, Math.round((e.loaded / e.total) * 100))
                })
                if (!res.ok) {
                    const err = await this.safeJson(res)
                    throw new Error(err?.message || `HTTP ${res.status}`)
                }
                this.result = await res.json()
                const count = this.result?.count ?? this.files.length
                this.clearAll()

                await Swal.fire({
                    icon: 'success',
                    title: 'アップロード完了',
                    html: `<div style="line-height:1.6">保存数：<b>${count}</b> 点<br>ご協力ありがとうございます。</div>`,
                    confirmButtonText: 'OK',
                })
            } catch (e) {
                console.error(e)
                Swal.fire({
                    icon: 'error',
                    title: 'アップロードに失敗しました',
                    text: (e && e.message) ? String(e.message) : '未知のエラーです',
                    confirmButtonText: '閉じる',
                })
            } finally {
                this.uploading = false
                this.progress = 0
            }
        },

        xhrPost(url, formData, onProgress) {
            return new Promise((resolve, reject) => {
                const xhr = new XMLHttpRequest()
                xhr.open('POST', url)
                xhr.responseType = 'json'
                xhr.setRequestHeader('X-CSRF-TOKEN', this.csrfToken)
                xhr.upload.onprogress = onProgress
                xhr.onload = () => {
                    resolve({
                        ok: xhr.status >= 200 && xhr.status < 300,
                        status: xhr.status,
                        json: async () => xhr.response,
                    })
                }
                xhr.onerror = () => reject(new Error('ネットワークエラー'))
                xhr.send(formData)
            })
        },

        async safeJson(res) {
            try { return await res.json() } catch { return null }
        },

        onLogoutClick() {
            if (confirm('退出するとこのセッションは終了します。よろしいですか？')) {
                try { history.replaceState(null, '', '/gate/ended') } catch {}
                this.$refs.logoutForm.submit()
            }
        },

        // 小さめトースト
        toast(message = '完了', icon = 'success') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top',
                showConfirmButton: false,
                timer: 1800,
                timerProgressBar: true,
            })
            return Toast.fire({ icon, title: message })
        },

        // =========================
        // カメラ & 画像処理
        // =========================
        async ensureAudioUnlocked() {
            if (this.audio.unlocked) return
            try {
                const AudioCtx = window.AudioContext || window.webkitAudioContext
                if (!AudioCtx) return
                this.audio.ctx = new AudioCtx()
                // iOS対策：極小音でアンロック
                const o = this.audio.ctx.createOscillator()
                const g = this.audio.ctx.createGain()
                g.gain.value = 0.0001
                o.connect(g).connect(this.audio.ctx.destination)
                o.start()
                o.stop(this.audio.ctx.currentTime + 0.02)
                this.audio.unlocked = true
            } catch (_) {}
        },
        playShutter() {
            if (!this.audio?.ctx) return
            const ctx = this.audio.ctx
            const now = ctx.currentTime
            const makePing = (freq, start, dur, gain = 0.15) => {
                const o = ctx.createOscillator()
                const g = ctx.createGain()
                o.type = 'square'
                o.frequency.setValueAtTime(freq, now + start)
                g.gain.setValueAtTime(0.0001, now + start)
                g.gain.exponentialRampToValueAtTime(gain, now + start + 0.01)
                g.gain.exponentialRampToValueAtTime(0.0001, now + start + dur)
                o.connect(g).connect(ctx.destination)
                o.start(now + start)
                o.stop(now + start + dur + 0.02)
            }
            makePing(1800, 0.00, 0.06) // ピッ
            makePing(900,  0.05, 0.08) // コッ
        },
        triggerFlash() {
            this.ui.flash = true
            setTimeout(() => (this.ui.flash = false), 120)
        },
        showShotMark() {
            this.ui.shotMark = true
            setTimeout(() => (this.ui.shotMark = false), 600)
        },
        announce(msg) {
            if (this.$refs.liveRegion) this.$refs.liveRegion.textContent = msg
        },

        async openCamera() {
            if (!navigator.mediaDevices?.getUserMedia) {
                Swal.fire({ icon: 'info', title: 'カメラ非対応', text: '写真の選択をご利用ください。' })
                return
            }
            try {
                this.camera.busy = true
                await this.ensureAudioUnlocked() // ユーザー操作直後にアンロック
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: this.camera.facing, width: { ideal: 1920 }, height: { ideal: 1080 } },
                    audio: false
                })
                this.camera.stream = stream
                this.camera.open = true
                await this.$nextTick()
                const video = this.$refs.videoRef
                video.srcObject = stream
                await video.play()
                this.announce('カメラを起動しました')
            } catch (e) {
                console.error(e)
                Swal.fire({ icon: 'error', title: 'カメラを起動できません', text: '権限設定をご確認ください。' })
            } finally {
                this.camera.busy = false
            }
        },
        async closeCamera() {
            if (this.camera.stream) {
                this.camera.stream.getTracks().forEach(t => t.stop())
                this.camera.stream = null
            }
            this.camera.open = false
        },
        async toggleFacing() {
            this.camera.facing = this.camera.facing === 'environment' ? 'user' : 'environment'
            await this.closeCamera()
            await this.openCamera()
        },
        async shoot() {
            if (!this.camera.open || this.ui.shutterLock) return
            this.ui.shutterLock = true
            this.camera.busy = true
            try {
                // 1) 体験：フラッシュ／バイブ／音
                this.triggerFlash()
                if (navigator.vibrate) navigator.vibrate(35)
                this.playShutter()

                // 2) 撮影ロジック
                const video = this.$refs.videoRef
                const w = video.videoWidth || 1280, h = video.videoHeight || 720
                const canvas = document.createElement('canvas')
                const { tw, th } = this.fitWithin(w, h, 2000, 2000)
                canvas.width = tw; canvas.height = th
                const ctx = canvas.getContext('2d')
                ctx.drawImage(video, 0, 0, tw, th)

                let quality = 0.92
                let blob = await this.canvasToBlob(canvas, 'image/jpeg', quality)
                while (blob.size > this.maxBytes && quality > 0.5) {
                    quality -= 0.08
                    blob = await this.canvasToBlob(canvas, 'image/jpeg', quality)
                }
                const name = `camera_${this.ts()}_${Math.random().toString(36).slice(2, 8)}.jpg`
                this.pushFile(blob, name)

                // 3) 視覚 & SR
                this.showShotMark()
                this.announce('撮影しました')
                this.toast('撮影しました')
            } catch (e) {
                console.error(e)
                Swal.fire({ icon: 'error', title: '撮影に失敗しました', text: 'もう一度お試しください。' })
            } finally {
                this.camera.busy = false
                setTimeout(() => (this.ui.shutterLock = false), 350) // 連写抑制
            }
        },

        // ユーティリティ
        safeName(original, fallbackExt = 'jpg') {
            const clean = (original || '').replace(/[^\w.\-]+/g, '_')
            return clean || `image_${this.ts()}.${fallbackExt}`
        },
        ts() {
            const d = new Date(), p = n => String(n).padStart(2, '0')
            return `${d.getFullYear()}${p(d.getMonth() + 1)}${p(d.getDate())}_${p(d.getHours())}${p(d.getMinutes())}${p(d.getSeconds())}`
        },
        fitWithin(w, h, mw, mh) {
            const r = Math.min(mw / w, mh / h, 1)
            return { tw: Math.round(w * r), th: Math.round(h * r) }
        },
        canvasToBlob(canvas, type, quality) {
            return new Promise(res => canvas.toBlob(b => res(b), type, quality))
        },
        async maybeCompress(file) {
            if (/image\/(heic|heif)/i.test(file.type)) return file
            try {
                const img = await this.readImage(file)
                const { tw, th } = this.fitWithin(img.width, img.height, 2000, 2000)
                const canvas = document.createElement('canvas')
                canvas.width = tw; canvas.height = th
                const ctx = canvas.getContext('2d')
                ctx.drawImage(img, 0, 0, tw, th)
                let quality = 0.92
                let blob = await this.canvasToBlob(canvas, 'image/jpeg', quality)
                while (blob.size > this.maxBytes && quality > 0.5) {
                    quality -= 0.08
                    blob = await this.canvasToBlob(canvas, 'image/jpeg', quality)
                }
                return blob
            } catch (e) {
                console.warn('compress failed, use original', e)
                return file
            }
        },
        readImage(file) {
            return new Promise((resolve, reject) => {
                const url = URL.createObjectURL(file)
                const img = new Image()
                img.onload = () => {
                    URL.revokeObjectURL(url)
                    resolve(img)
                }
                img.onerror = e => {
                    URL.revokeObjectURL(url)
                    reject(e)
                }
                img.src = url
            })
        },
    },
}
</script>

<style scoped>
html, body {
    background: #f8fafc;
}

.thumb {
    position: relative;
}

.thumb-img {
    width: 100%;
    aspect-ratio: 1/1;
    object-fit: cover;
    display: block;
}

.thumb-fallback {
    aspect-ratio: 1/1;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f1f5f9;
}

.remove-btn {
    position: absolute;
    top: .25rem;
    right: .25rem;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: none;
    z-index: 5;
    color: #fff;
    background: rgba(220, 53, 69, .95);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
    cursor: pointer;
}

.badge {
    font-weight: 600;
    pointer-events: none;
}

.action-footer {
    position: fixed;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 10;
    padding-bottom: max(env(safe-area-inset-bottom), 8px);
}

.with-footer {
    padding-bottom: calc(88px + env(safe-area-inset-bottom));
}

.action-footer .btn-group {
    display: flex;
    width: 100%;
}

.action-footer .btn-group .btn {
    flex: 1 1 0;
}

.cam-overlay {
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: #000;
    color: #fff;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.cam-video {
    width: 100%;
    height: auto;
    max-height: calc(100vh - 140px);
    object-fit: contain;
}

.cam-toolbar {
    position: absolute;
    top: 12px;
    left: 12px;
    right: 12px;
    display: flex;
    justify-content: space-between;
    gap: 8px;
}

.cam-bottom {
    position: absolute;
    bottom: 16px;
    left: 0;
    right: 0;
    display: flex;
    justify-content: center;
}

.cam-shutter {
    min-width: 220px;
    box-shadow: 0 8px 22px rgba(0, 0, 0, .35);
}

/* 白フラッシュ */
.flash {
    position: fixed;
    inset: 0;
    background: #fff;
    opacity: 0;
    pointer-events: none;
    z-index: 10000;
    transition: opacity 120ms ease;
}
.flash.active { opacity: 0.9; }

/* 撮影マーク（中央） */
.shot-mark {
    position: fixed;
    left: 50%;
    top: 40%;
    transform: translate(-50%, -50%);
    background: rgba(0,0,0,.65);
    color: #fff;
    padding: .6rem 1rem;
    border-radius: 9999px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    z-index: 10001;
    backdrop-filter: blur(2px);
}

/* iOS等の見た目安定化 */
.cam-shutter:disabled {
    opacity: .7;
    cursor: not-allowed;
}
</style>
