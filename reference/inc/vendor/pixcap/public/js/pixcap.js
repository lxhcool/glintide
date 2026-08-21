/*!
 * Pixcap - 无感人机验证（前端控件）
 *
 * 依赖：无（纯原生 JS，含内置 SHA-256，不依赖 WebCrypto，可在 http 环境下运行）
 *
 * 用法：
 *   var instance = new window.Pixcap({
 *       mode: 'bubble',          // 'bubble' | 'inline' | 'hidden'
 *       container: el,           // bubble/inline 模式的挂载容器（hidden 传 null）
 *       button: null,
 *       apiEndpoint: 挑战地址,
 *       verifyEndpoint: 校验地址,
 *       theme: 'business',
 *       size: 'compact',
 *       logoUrl: '',
 *       language: 'zh-CN',
 *       initialState: 'verifying',
 *       minVerifyingMs: 1400,
 *       showExpireCountdown: false,
 *       onSuccess: function(payload){},
 *       onError: function(error){}
 *   });
 *   instance.verify();
 */
(function (global) {
    'use strict';

    /* ---------------- SHA-256（纯 JS） ---------------- */
    var K = [
        0x428a2f98, 0x71374491, 0xb5c0fbcf, 0xe9b5dba5, 0x3956c25b, 0x59f111f1, 0x923f82a4, 0xab1c5ed5,
        0xd807aa98, 0x12835b01, 0x243185be, 0x550c7dc3, 0x72be5d74, 0x80deb1fe, 0x9bdc06a7, 0xc19bf174,
        0xe49b69c1, 0xefbe4786, 0x0fc19dc6, 0x240ca1cc, 0x2de92c6f, 0x4a7484aa, 0x5cb0a9dc, 0x76f988da,
        0x983e5152, 0xa831c66d, 0xb00327c8, 0xbf597fc7, 0xc6e00bf3, 0xd5a79147, 0x06ca6351, 0x14292967,
        0x27b70a85, 0x2e1b2138, 0x4d2c6dfc, 0x53380d13, 0x650a7354, 0x766a0abb, 0x81c2c92e, 0x92722c85,
        0xa2bfe8a1, 0xa81a664b, 0xc24b8b70, 0xc76c51a3, 0xd192e819, 0xd6990624, 0xf40e3585, 0x106aa070,
        0x19a4c116, 0x1e376c08, 0x2748774c, 0x34b0bcb5, 0x391c0cb3, 0x4ed8aa4a, 0x5b9cca4f, 0x682e6ff3,
        0x748f82ee, 0x78a5636f, 0x84c87814, 0x8cc70208, 0x90befffa, 0xa4506ceb, 0xbef9a3f7, 0xc67178f2
    ];

    var H0 = 0x6a09e667, H1 = 0xbb67ae85, H2 = 0x3c6ef372, H3 = 0xa54ff53a,
        H4 = 0x510e527f, H5 = 0x9b05688c, H6 = 0x1f83d9ab, H7 = 0x5be0cd19;

    function rotr(x, n) { return (x >>> n) | (x << (32 - n)); }

    function sha256Hex(str) {
        var i, j;
        var len = str.length;
        var blocks = ((len + 9) >> 6) + 1;
        var w = new Array(64);
        var m = new Array(blocks * 16);

        for (i = 0; i < m.length; i++) { m[i] = 0; }
        for (i = 0; i < len; i++) {
            m[i >> 2] |= (str.charCodeAt(i) & 0xff) << (24 - (i & 3) * 8);
        }
        m[len >> 2] |= 0x80 << (24 - (len & 3) * 8);
        m[blocks * 16 - 1] = len * 8;

        var a = H0, b = H1, c = H2, d = H3, e = H4, f = H5, g = H6, h = H7;

        for (i = 0; i < blocks; i++) {
            for (j = 0; j < 16; j++) { w[j] = m[i * 16 + j]; }
            for (j = 16; j < 64; j++) {
                var s0 = rotr(w[j - 15], 7) ^ rotr(w[j - 15], 18) ^ (w[j - 15] >>> 3);
                var s1 = rotr(w[j - 2], 17) ^ rotr(w[j - 2], 19) ^ (w[j - 2] >>> 10);
                w[j] = (w[j - 16] + s0 + w[j - 7] + s1) | 0;
            }

            var A = a, B = b, C = c, D = d, E = e, F = f, G = g, H = h;

            for (j = 0; j < 64; j++) {
                var S1 = rotr(E, 6) ^ rotr(E, 11) ^ rotr(E, 25);
                var ch = (E & F) ^ (~E & G);
                var t1 = (H + S1 + ch + K[j] + w[j]) | 0;
                var S0 = rotr(A, 2) ^ rotr(A, 13) ^ rotr(A, 22);
                var maj = (A & B) ^ (A & C) ^ (B & C);
                var t2 = (S0 + maj) | 0;

                H = G; G = F; F = E; E = (D + t1) | 0;
                D = C; C = B; B = A; A = (t1 + t2) | 0;
            }

            a = (a + A) | 0; b = (b + B) | 0; c = (c + C) | 0; d = (d + D) | 0;
            e = (e + E) | 0; f = (f + F) | 0; g = (g + G) | 0; h = (h + H) | 0;
        }

        var hex = '';
        var hh = [a, b, c, d, e, f, g, h];
        for (i = 0; i < 8; i++) {
            var v = hh[i] >>> 0;
            hex += ('00000000' + v.toString(16)).slice(-8);
        }
        return hex;
    }

    /* ---------------- 工作量证明求解 ---------------- */
    function solveProof(challenge, cost, maxIter) {
        maxIter = maxIter || 5000000;
        var n = 0;
        while (n < maxIter) {
            var nonce = n.toString(16);
            var digest = sha256Hex(challenge + nonce);
            var value = parseInt(digest.slice(0, 6), 16);
            if (value % cost === 0) {
                return nonce;
            }
            n++;
        }
        return null;
    }

    /* ---------------- 工具 ---------------- */
    function el(tag, className, text) {
        var node = document.createElement(tag);
        if (className) { node.className = className; }
        if (text !== undefined && text !== null) { node.textContent = text; }
        return node;
    }

    function postJSON(url, data) {
        return fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        }).then(function (res) {
            if (!res.ok) { throw new Error('HTTP ' + res.status); }
            return res.json();
        });
    }

    /* ---------------- Pixcap 控件 ---------------- */
    function Pixcap(options) {
        options = options || {};
        this.options = options;
        this.mode = options.mode || 'bubble';
        this.container = options.container || null;
        this.apiEndpoint = options.apiEndpoint || '';
        this.verifyEndpoint = options.verifyEndpoint || '';
        this.theme = options.theme || 'business';
        this.language = options.language || 'zh-CN';
        this.minVerifyingMs = Math.max(0, options.minVerifyingMs || 0);
        this.showExpireCountdown = !!options.showExpireCountdown;
        this.onSuccess = typeof options.onSuccess === 'function' ? options.onSuccess : function () {};
        this.onError = typeof options.onError === 'function' ? options.onError : function () {};

        this._busy = false;
        this._destroyed = false;
        this._payload = null;
        this._countdownTimer = null;
        this._expireTimer = null;

        this._labels = this.language && this.language.indexOf('zh') === 0 ? {
            verifying: '正在验证…',
            verified: '验证通过',
            failed: '验证失败，请重试',
            unverified: '安全验证',
            expire: '即将过期'
        } : {
            verifying: 'Verifying…',
            verified: 'Verified',
            failed: 'Verification failed',
            unverified: 'Security check',
            expire: 'Expiring'
        };

        this._buildDOM();

        if ((options.initialState || 'verifying') === 'verifying') {
            this._setState('verifying');
        } else {
            this._setState('unverified');
        }
    }

    Pixcap.prototype._buildDOM = function () {
        if (this.mode === 'hidden' || !this.container) {
            this._widget = null;
            return;
        }

        var widget = el('div', 'pixcap-widget pixcap-floating-show');
        widget.setAttribute('data-pixcap-theme', this.theme);

        var main = el('div', 'pixcap-main');

        var brand = el('div', 'pixcap-brand-area');
        if (this.options.logoUrl) {
            var logo = el('img', 'pixcap-brand-logo');
            logo.src = this.options.logoUrl;
            logo.alt = 'Pixcap';
            brand.appendChild(logo);
        }
        brand.appendChild(el('span', 'pixcap-brand-name', 'Pixcap'));
        main.appendChild(brand);

        this._icon = el('span', 'pixcap-icon');
        main.appendChild(this._icon);

        this._label = el('span', 'pixcap-label', this._labels.verifying);
        main.appendChild(this._label);

        this._expireInfo = el('span', 'pixcap-expire-info', '');
        main.appendChild(this._expireInfo);

        this._cooldownMsg = el('span', 'pixcap-cooldown-msg', '');
        main.appendChild(this._cooldownMsg);

        widget.appendChild(main);
        this._widget = widget;

        if (this.mode === 'bubble') {
            var floating = el('div', 'pixcap-floating-container');
            floating.appendChild(widget);
            this.container.appendChild(floating);
        } else {
            this.container.appendChild(widget);
        }
    };

    Pixcap.prototype._setState = function (state) {
        this._state = state;
        var icon = this._icon;
        if (!icon) { return; }
        icon.className = 'pixcap-icon';
        if (state === 'verifying') {
            icon.classList.add('pixcap-icon-spin');
        } else if (state === 'verified') {
            icon.classList.add('pixcap-icon-check');
        } else if (state === 'failed') {
            icon.classList.add('pixcap-icon-cross');
        }
        if (this._label) {
            this._label.textContent = this._labels[state] || this._labels.verifying;
        }
        var widget = this._widget;
        if (widget) {
            widget.classList.remove('pixcap-state-verifying', 'pixcap-state-verified', 'pixcap-state-failed', 'pixcap-state-unverified');
            widget.classList.add('pixcap-state-' + state);
        }
    };

    Pixcap.prototype._startExpireCountdown = function (expires) {
        var self = this;
        this._clearExpireCountdown();
        if (!this.showExpireCountdown || !expires || !this._expireInfo) { return; }

        var tick = function () {
            var remain = expires - Math.floor(Date.now() / 1000);
            if (remain <= 0) {
                self._expireInfo.textContent = '';
                self.reset();
                self.verify();
                return;
            }
            self._expireInfo.textContent = remain + 's';
        };
        tick();
        this._countdownTimer = setInterval(tick, 1000);
    };

    Pixcap.prototype._clearExpireCountdown = function () {
        if (this._countdownTimer) {
            clearInterval(this._countdownTimer);
            this._countdownTimer = null;
        }
        if (this._expireTimer) {
            clearTimeout(this._expireTimer);
            this._expireTimer = null;
        }
        if (this._expireInfo) { this._expireInfo.textContent = ''; }
    };

    Pixcap.prototype.reset = function () {
        this._clearExpireCountdown();
        this._payload = null;
        this._setState('unverified');
        return this;
    };

    Pixcap.prototype._finish = function (payload) {
        var self = this;
        this._payload = payload;
        var elapsed = Date.now() - this._startAt;
        var wait = Math.max(0, this.minVerifyingMs - elapsed);

        if (this._destroyed) { return Promise.resolve(payload); }

        return new Promise(function (resolve) {
            setTimeout(function () {
                self._setState('verified');
                self.onSuccess(payload);
                resolve(payload);
            }, wait);
        });
    };

    Pixcap.prototype._fail = function (err) {
        var self = this;
        var error = err instanceof Error ? err : new Error((err && err.message) || this._labels.failed);
        this._setState('failed');
        if (!this._destroyed) {
            this.onError(error);
        }
        return Promise.reject(error);
    };

    Pixcap.prototype.verify = function () {
        var self = this;
        if (this._busy) {
            return this._promise || Promise.reject(new Error('busy'));
        }
        this._busy = true;
        this._startAt = Date.now();
        this._setState('verifying');

        this._promise = this._run().then(function (payload) {
            self._busy = false;
            return payload;
        }, function (err) {
            self._busy = false;
            throw err;
        });

        return this._promise;
    };

    Pixcap.prototype._run = function () {
        var self = this;
        if (!this.apiEndpoint || !this.verifyEndpoint) {
            return this._fail(new Error('Pixcap 参数缺失，请联系管理员'));
        }

        return postJSON(this.apiEndpoint, {}).then(function (challenge) {
            if (!challenge || !challenge.challenge) {
                throw new Error('Pixcap 挑战获取失败，请刷新重试');
            }

            var cost = challenge.cost || 50000;
            var nonce = solveProof(challenge.challenge, cost);
            if (nonce === null) {
                throw new Error('Pixcap 计算超时，请重试');
            }

            var payload = {
                challenge: challenge.challenge,
                algorithm: challenge.algorithm,
                cost: challenge.cost,
                keyLength: challenge.keyLength,
                expires: challenge.expires,
                signature: challenge.signature,
                nonce: nonce
            };

            self._startExpireCountdown(challenge.expires);

            return postJSON(self.verifyEndpoint, { payload: payload }).then(function (res) {
                if (res && (res.verified === true || res.success === true)) {
                    return self._finish(payload);
                }
                if (res && res.expired) {
                    throw new Error(self._labels.expire + '，请重试');
                }
                if (res && res.invalidSignature) {
                    throw new Error('验证签名无效，请刷新后重试');
                }
                throw new Error((res && res.message) || self._labels.failed);
            });
        }).catch(function (err) {
            return self._fail(err);
        });
    };

    Pixcap.prototype.destroy = function () {
        this._destroyed = true;
        this._clearExpireCountdown();
        if (this._widget && this._widget.parentNode) {
            this._widget.parentNode.removeChild(this._widget);
        }
        this._widget = null;
    };

    global.Pixcap = Pixcap;
})(window);
