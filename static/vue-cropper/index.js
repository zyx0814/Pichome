var __vite_style__ = document.createElement("style");
__vite_style__.innerHTML = '.vue-cropper[data-v-48aab112]{position:relative;width:100%;height:100%;box-sizing:border-box;user-select:none;-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;direction:ltr;touch-action:none;text-align:left;background-image:url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQAQMAAAAlPW0iAAAAA3NCSVQICAjb4U/gAAAABlBMVEXMzMz////TjRV2AAAACXBIWXMAAArrAAAK6wGCiw1aAAAAHHRFWHRTb2Z0d2FyZQBBZG9iZSBGaXJld29ya3MgQ1M26LyyjAAAABFJREFUCJlj+M/AgBVhF/0PAH6/D/HkDxOGAAAAAElFTkSuQmCC")}.cropper-box-canvas[data-v-48aab112],.cropper-box[data-v-48aab112],.cropper-crop-box[data-v-48aab112],.cropper-drag-box[data-v-48aab112],.cropper-face[data-v-48aab112]{position:absolute;top:0;right:0;bottom:0;left:0;user-select:none}.cropper-box-canvas img[data-v-48aab112]{position:relative;text-align:left;user-select:none;transform:none;max-width:none;max-height:none}.cropper-box[data-v-48aab112]{overflow:hidden}.cropper-move[data-v-48aab112]{cursor:move}.cropper-crop[data-v-48aab112]{cursor:crosshair}.cropper-modal[data-v-48aab112]{background:rgba(0,0,0,.5)}.cropper-view-box[data-v-48aab112]{display:block;overflow:hidden;width:100%;height:100%;outline:1px solid #39f;outline-color:rgba(51,153,255,.75);user-select:none}.cropper-view-box img[data-v-48aab112]{user-select:none;text-align:left;max-width:none;max-height:none}.cropper-face[data-v-48aab112]{top:0;left:0;background-color:#fff;opacity:.1}.crop-info[data-v-48aab112]{position:absolute;left:0;min-width:65px;text-align:center;color:#fff;line-height:20px;background-color:rgba(0,0,0,.8);font-size:12px}.crop-line[data-v-48aab112]{position:absolute;display:block;width:100%;height:100%;opacity:.1}.line-w[data-v-48aab112]{top:-3px;left:0;height:5px;cursor:n-resize}.line-a[data-v-48aab112]{top:0;left:-3px;width:5px;cursor:w-resize}.line-s[data-v-48aab112]{bottom:-3px;left:0;height:5px;cursor:s-resize}.line-d[data-v-48aab112]{top:0;right:-3px;width:5px;cursor:e-resize}.crop-point[data-v-48aab112]{position:absolute;width:8px;height:8px;opacity:.75;background-color:#39f;border-radius:100%}.point1[data-v-48aab112]{top:-4px;left:-4px;cursor:nw-resize}.point2[data-v-48aab112]{top:-5px;left:50%;margin-left:-3px;cursor:n-resize}.point3[data-v-48aab112]{top:-4px;right:-4px;cursor:ne-resize}.point4[data-v-48aab112]{top:50%;left:-4px;margin-top:-3px;cursor:w-resize}.point5[data-v-48aab112]{top:50%;right:-4px;margin-top:-3px;cursor:e-resize}.point6[data-v-48aab112]{bottom:-5px;left:-4px;cursor:sw-resize}.point7[data-v-48aab112]{bottom:-5px;left:50%;margin-left:-3px;cursor:s-resize}.point8[data-v-48aab112]{bottom:-5px;right:-4px;cursor:se-resize}@media screen and (max-width:500px){.crop-point[data-v-48aab112]{position:absolute;width:20px;height:20px;opacity:.45;background-color:#39f;border-radius:100%}.point1[data-v-48aab112]{top:-10px;left:-10px}.point2[data-v-48aab112],.point4[data-v-48aab112],.point5[data-v-48aab112],.point7[data-v-48aab112]{display:none}.point3[data-v-48aab112]{top:-10px;right:-10px}.point4[data-v-48aab112]{top:0;left:0}.point6[data-v-48aab112]{bottom:-10px;left:-10px}.point8[data-v-48aab112]{bottom:-10px;right:-10px}}', document.head.appendChild(__vite_style__), function (t, e) {
    "object" == typeof exports && "undefined" != typeof module ? e(exports, require("vue")) : "function" == typeof define && define.amd ? define(["exports", "vue"], e) : e((t = "undefined" != typeof globalThis ? globalThis : t || self)["vue-cropper"] = {}, t.Vue)
}(this, (function (t, e) {
    "use strict";
    const i = {};
    i.getData = t => new Promise(((e, i) => {
        let s = {};
        (function (t) {
            let e = null;
            return new Promise(((i, s) => {
                if (t.src) if (/^data\:/i.test(t.src)) e = function (t) {
                    t = t.replace(/^data\:([^\;]+)\;base64,/gim, "");
                    for (var e = atob(t), i = e.length, s = new ArrayBuffer(i), o = new Uint8Array(s), r = 0; r < i; r++) o[r] = e.charCodeAt(r);
                    return s
                }(t.src), i(e); else if (/^blob\:/i.test(t.src)) {
                    var o = new FileReader;
                    o.onload = function (t) {
                        e = t.target.result, i(e)
                    }, function (t, e) {
                        var i = new XMLHttpRequest;
                        i.open("GET", t, !0), i.responseType = "blob", i.onload = function (t) {
                            200 != this.status && 0 !== this.status || e(this.response)
                        }, i.send()
                    }(t.src, (function (t) {
                        o.readAsArrayBuffer(t)
                    }))
                } else {
                    var r = new XMLHttpRequest;
                    r.onload = function () {
                        if (200 != this.status && 0 !== this.status) throw "Could not load image";
                        e = r.response, i(e), r = null
                    }, r.open("GET", t.src, !0), r.responseType = "arraybuffer", r.send(null)
                } else s("img error")
            }))
        })(t).then((t => {
            s.arrayBuffer = t, s.orientation = function (t) {
                var e, i, s, o, r, h, a, c, n, p = new DataView(t), l = p.byteLength;
                if (255 === p.getUint8(0) && 216 === p.getUint8(1)) for (c = 2; c < l;) {
                    if (255 === p.getUint8(c) && 225 === p.getUint8(c + 1)) {
                        h = c;
                        break
                    }
                    c++
                }
                h && (i = h + 10, "Exif" === function (t, e, i) {
                    var s, o = "";
                    for (s = e, i += e; s < i; s++) o += String.fromCharCode(t.getUint8(s));
                    return o
                }(p, h + 4, 4) && ((o = 18761 === (r = p.getUint16(i))) || 19789 === r) && 42 === p.getUint16(i + 2, o) && (s = p.getUint32(i + 4, o)) >= 8 && (a = i + s));
                if (a) for (l = p.getUint16(a, o), n = 0; n < l; n++) if (c = a + 12 * n + 2, 274 === p.getUint16(c, o)) {
                    c += 8, e = p.getUint16(c, o);
                    break
                }
                return e
            }(t), e(s)
        })).catch((t => {
            i(t)
        }))
    }));
    const s = e.defineComponent({
        data: function () {
            return {
                w: 0,
                h: 0,
                scale: 1,
                x: 0,
                y: 0,
                loading: !0,
                trueWidth: 0,
                trueHeight: 0,
                move: !0,
                moveX: 0,
                moveY: 0,
                crop: !1,
                cropping: !1,
                cropW: 0,
                cropH: 0,
                cropOldW: 0,
                cropOldH: 0,
                canChangeX: !1,
                canChangeY: !1,
                changeCropTypeX: 1,
                changeCropTypeY: 1,
                cropX: 0,
                cropY: 0,
                cropChangeX: 0,
                cropChangeY: 0,
                cropOffsertX: 0,
                cropOffsertY: 0,
                support: "",
                touches: [],
                touchNow: !1,
                rotate: 0,
                isIos: !1,
                orientation: 0,
                imgs: "",
                coe: .2,
                scaling: !1,
                scalingSet: "",
                coeStatus: "",
                isCanShow: !0
            }
        },
        props: {
            img: {type: [String, Blob, null, File], default: ""},
            outputSize: {type: Number, default: 1},
            outputType: {type: String, default: "jpeg"},
            info: {type: Boolean, default: !0},
            canScale: {type: Boolean, default: !0},
            autoCrop: {type: Boolean, default: !1},
            autoCropWidth: {type: [Number, String], default: 0},
            autoCropHeight: {type: [Number, String], default: 0},
            autoCropX: {type: [Number, String], default: 0},
            autoCropY: {type: [Number, String], default: 0},
            fixed: {type: Boolean, default: !1},
            fixedNumber: {type: Array, default: () => [1, 1]},
            fixedBox: {type: Boolean, default: !1},
            full: {type: Boolean, default: !1},
            canMove: {type: Boolean, default: !0},
            canMoveBox: {type: Boolean, default: !0},
            original: {type: Boolean, default: !1},
            centerBox: {type: Boolean, default: !1},
            high: {type: Boolean, default: !0},
            infoTrue: {type: Boolean, default: !1},
            maxImgSize: {type: [Number, String], default: 2e3},
            enlarge: {type: [Number, String], default: 1},
            preW: {type: [Number, String], default: 0},
            mode: {type: String, default: "contain"},
            limitMinSize: {type: [Number, Array, String], default: () => 10}
        },
        computed: {

            cropInfo() {
                let t = {};
                if (t.top = this.cropOffsertY > 21 ? "-21px" : "0px", t.width = this.cropW > 0 ? this.cropW : 0, t.height = this.cropH > 0 ? this.cropH : 0, this.infoTrue) {
                    let e = 1;
                    this.high && !this.full && (e = window.devicePixelRatio), 1 !== this.enlarge & !this.full && (e = Math.abs(Number(this.enlarge))), t.width = t.width * e, t.height = t.height * e, this.full && (t.width = t.width / this.scale, t.height = t.height / this.scale)
                }
                return t.width = t.width.toFixed(0), t.height = t.height.toFixed(0), t
            }, isIE: () => !!window.ActiveXObject || "ActiveXObject" in window, passive() {
                return this.isIE ? null : {passive: !1}
            }
        },
        watch: {

            img() {
                this.checkedImg()
            }, imgs(t) {
                "" !== t && this.reload();
            }, cropW() {
                this.showPreview();
            }, cropH() {
                this.showPreview();
            }, autoCropX(t) {
                 this.cropOffsertX = t, this.showPreview()
            },autoCropY(t) {
               this.cropOffsertY = t,this.showPreview()
            },cropOffsertX() {
                this.showPreview()
            }, cropOffsertY() {
                this.showPreview()
            }, scale(t, e) {
                this.showPreview()
            }, x() {
                this.showPreview()
            }, y() {
                this.showPreview()
            }, autoCrop(t) {
                t && this.goAutoCrop()
            }, autoCropWidth() {
                this.autoCrop && this.goAutoCrop()
            }, autoCropHeight() {
                this.autoCrop && this.goAutoCrop()
            }, mode() {
                this.checkedImg()
            }, rotate() {
                this.showPreview(), (this.autoCrop || this.cropW > 0 || this.cropH > 0) && this.goAutoCrop(this.cropW, this.cropH)
            }
        },
        methods: {
            getVersion(t) {
                var e = navigator.userAgent.split(" "), i = "";
                let s = 0;
                const o = new RegExp(t, "i");
                for (var r = 0; r < e.length; r++) o.test(e[r]) && (i = e[r]);
                return s = i ? i.split("/")[1].split(".") : ["0", "0", "0"], s
            }, checkOrientationImage(t, e, i, s) {
                if (this.getVersion("chrome")[0] >= 81) e = -1; else if (this.getVersion("safari")[0] >= 605) {
                    const t = this.getVersion("version");
                    t[0] > 13 && t[1] > 1 && (e = -1)
                } else {
                    const t = navigator.userAgent.toLowerCase().match(/cpu iphone os (.*?) like mac os/);
                    if (t) {
                        let i = t[1];
                        i = i.split("_"), (i[0] > 13 || i[0] >= 13 && i[1] >= 4) && (e = -1)
                    }
                }
                let o = document.createElement("canvas"), r = o.getContext("2d");
                switch (r.save(), e) {
                    case 2:
                        o.width = i, o.height = s, r.translate(i, 0), r.scale(-1, 1);
                        break;
                    case 3:
                        o.width = i, o.height = s, r.translate(i / 2, s / 2), r.rotate(180 * Math.PI / 180), r.translate(-i / 2, -s / 2);
                        break;
                    case 4:
                        o.width = i, o.height = s, r.translate(0, s), r.scale(1, -1);
                        break;
                    case 5:
                        o.height = i, o.width = s, r.rotate(.5 * Math.PI), r.scale(1, -1);
                        break;
                    case 6:
                        o.width = s, o.height = i, r.translate(s / 2, i / 2), r.rotate(90 * Math.PI / 180), r.translate(-i / 2, -s / 2);
                        break;
                    case 7:
                        o.height = i, o.width = s, r.rotate(.5 * Math.PI), r.translate(i, -s), r.scale(-1, 1);
                        break;
                    case 8:
                        o.height = i, o.width = s, r.translate(s / 2, i / 2), r.rotate(-90 * Math.PI / 180), r.translate(-i / 2, -s / 2);
                        break;
                    default:
                        o.width = i, o.height = s
                }
                r.drawImage(t, 0, 0, i, s), r.restore(), o.toBlob((t => {
                    let e = URL.createObjectURL(t);
                    URL.revokeObjectURL(this.imgs), this.imgs = e
                }), "image/" + this.outputType, 1)
            }, checkedImg() {
                if (null === this.img || "" === this.img) return this.imgs = "", void this.clearCrop();
                this.loading = !0, this.scale = 1, this.rotate = 0, this.clearCrop();
                let t = new Image;
                if (t.onload = () => {
                    if ("" === this.img) return this.$emit("imgLoad", "error"), this.$emit("img-load", "error"), !1;
                    let e = t.width, s = t.height;
                    i.getData(t).then((i => {
                        this.orientation = i.orientation || 1;
                        let o = Number(this.maxImgSize);
                        !this.orientation && e < o & s < o ? this.imgs = this.img : (e > o && (s = s / e * o, e = o), s > o && (e = e / s * o, s = o), this.checkOrientationImage(t, this.orientation, e, s))
                    }))
                }, t.onerror = () => {
                    this.$emit("imgLoad", "error"), this.$emit("img-load", "error")
                }, "data" !== this.img.substr(0, 4) && (t.crossOrigin = ""), this.isIE) {
                    var e = new XMLHttpRequest;
                    e.onload = function () {
                        var e = URL.createObjectURL(this.response);
                        t.src = e
                    }, e.open("GET", this.img, !0), e.responseType = "blob", e.send()
                } else t.src = this.img
            }, startMove(t) {
                if (t.preventDefault(), this.move && !this.crop) {
                    if (!this.canMove) return !1;
                    this.moveX = ("clientX" in t ? t.clientX : t.touches[0].clientX) - this.x, this.moveY = ("clientY" in t ? t.clientY : t.touches[0].clientY) - this.y, t.touches ? (window.addEventListener("touchmove", this.moveImg), window.addEventListener("touchend", this.leaveImg), 2 == t.touches.length && (this.touches = t.touches, window.addEventListener("touchmove", this.touchScale), window.addEventListener("touchend", this.cancelTouchScale))) : (window.addEventListener("mousemove", this.moveImg), window.addEventListener("mouseup", this.leaveImg)), this.$emit("imgMoving", {
                        moving: !0,
                        axis: this.getImgAxis()
                    }), this.$emit("img-moving", {moving: !0, axis: this.getImgAxis()})
                } else this.cropping = !0, window.addEventListener("mousemove", this.createCrop), window.addEventListener("mouseup", this.endCrop), window.addEventListener("touchmove", this.createCrop), window.addEventListener("touchend", this.endCrop), this.cropOffsertX = t.offsetX ? t.offsetX : t.touches[0].pageX - this.$refs.cropper.offsetLeft, this.cropOffsertY = t.offsetY ? t.offsetY : t.touches[0].pageY - this.$refs.cropper.offsetTop, this.cropX = "clientX" in t ? t.clientX : t.touches[0].clientX, this.cropY = "clientY" in t ? t.clientY : t.touches[0].clientY, this.cropChangeX = this.cropOffsertX, this.cropChangeY = this.cropOffsertY, this.cropW = 0, this.cropH = 0
            }, touchScale(t) {
                t.preventDefault();
                let e = this.scale;
                var i = this.touches[0].clientX, s = this.touches[0].clientY, o = t.touches[0].clientX,
                    r = t.touches[0].clientY, h = this.touches[1].clientX, a = this.touches[1].clientY,
                    c = t.touches[1].clientX, n = t.touches[1].clientY,
                    p = Math.sqrt(Math.pow(i - h, 2) + Math.pow(s - a, 2)),
                    l = Math.sqrt(Math.pow(o - c, 2) + Math.pow(r - n, 2)) - p, d = 1,
                    u = (d = (d = d / this.trueWidth > d / this.trueHeight ? d / this.trueHeight : d / this.trueWidth) > .1 ? .1 : d) * l;
                if (!this.touchNow) {
                    if (this.touchNow = !0, l > 0 ? e += Math.abs(u) : l < 0 && e > Math.abs(u) && (e -= Math.abs(u)), this.touches = t.touches, setTimeout((() => {
                        this.touchNow = !1
                    }), 8), !this.checkoutImgAxis(this.x, this.y, e)) return !1;
                    this.scale = e
                }
            }, cancelTouchScale(t) {
                window.removeEventListener("touchmove", this.touchScale)
            }, moveImg(t) {
                if (t.preventDefault(), t.touches && 2 === t.touches.length) return this.touches = t.touches, window.addEventListener("touchmove", this.touchScale), window.addEventListener("touchend", this.cancelTouchScale), window.removeEventListener("touchmove", this.moveImg), !1;
                let e, i, s = "clientX" in t ? t.clientX : t.touches[0].clientX,
                    o = "clientY" in t ? t.clientY : t.touches[0].clientY;
                e = s - this.moveX, i = o - this.moveY, this.$nextTick((() => {
                    if (this.centerBox) {
                        let t, s, o, r, h = this.getImgAxis(e, i, this.scale), a = this.getCropAxis(),
                            c = this.trueHeight * this.scale, n = this.trueWidth * this.scale;
                        switch (this.rotate) {
                            case 1:
                            case-1:
                            case 3:
                            case-3:
                                t = this.cropOffsertX - this.trueWidth * (1 - this.scale) / 2 + (c - n) / 2, s = this.cropOffsertY - this.trueHeight * (1 - this.scale) / 2 + (n - c) / 2, o = t - c + this.cropW, r = s - n + this.cropH;
                                break;
                            default:
                                t = this.cropOffsertX - this.trueWidth * (1 - this.scale) / 2, s = this.cropOffsertY - this.trueHeight * (1 - this.scale) / 2, o = t - n + this.cropW, r = s - c + this.cropH
                        }
                        h.x1 >= a.x1 && (e = t), h.y1 >= a.y1 && (i = s), h.x2 <= a.x2 && (e = o), h.y2 <= a.y2 && (i = r)
                    }
                    this.x = e, this.y = i, this.$emit("imgMoving", {
                        moving: !0,
                        axis: this.getImgAxis()
                    }), this.$emit("img-moving", {moving: !0, axis: this.getImgAxis()})
                }))
            }, leaveImg(t) {
                window.removeEventListener("mousemove", this.moveImg), window.removeEventListener("touchmove", this.moveImg), window.removeEventListener("mouseup", this.leaveImg), window.removeEventListener("touchend", this.leaveImg), this.$emit("imgMoving", {
                    moving: !1,
                    axis: this.getImgAxis()
                }), this.$emit("img-moving", {moving: !1, axis: this.getImgAxis()})
            }, scaleImg() {
                this.canScale && window.addEventListener(this.support, this.changeSize, this.passive)
            }, cancelScale() {
                this.canScale && window.removeEventListener(this.support, this.changeSize)
            }, changeSize(t) {
                t.preventDefault();
                let e = this.scale;
                var i = t.deltaY || t.wheelDelta;
                i = navigator.userAgent.indexOf("Firefox") > 0 ? 30 * i : i, this.isIE && (i = -i);
                var s = this.coe,
                    o = (s = s / this.trueWidth > s / this.trueHeight ? s / this.trueHeight : s / this.trueWidth) * i;
                o < 0 ? e += Math.abs(o) : e > Math.abs(o) && (e -= Math.abs(o));
                let r = o < 0 ? "add" : "reduce";
                if (r !== this.coeStatus && (this.coeStatus = r, this.coe = .2), this.scaling || (this.scalingSet = setTimeout((() => {
                    this.scaling = !1, this.coe = this.coe += .01
                }), 50)), this.scaling = !0, !this.checkoutImgAxis(this.x, this.y, e)) return !1;
                this.scale = e
            }, changeScale(t) {
                let e = this.scale;
                t = t || 1;
                var i = 20;
                if ((t *= i = i / this.trueWidth > i / this.trueHeight ? i / this.trueHeight : i / this.trueWidth) > 0 ? e += Math.abs(t) : e > Math.abs(t) && (e -= Math.abs(t)), !this.checkoutImgAxis(this.x, this.y, e)) return !1;
                this.scale = e
            }, createCrop(t) {
                t.preventDefault();
                var e = "clientX" in t ? t.clientX : t.touches ? t.touches[0].clientX : 0,
                    i = "clientY" in t ? t.clientY : t.touches ? t.touches[0].clientY : 0;
                this.$nextTick((() => {
                    var t = e - this.cropX, s = i - this.cropY;
                    if (t > 0 ? (this.cropW = t + this.cropChangeX > this.w ? this.w - this.cropChangeX : t, this.cropOffsertX = this.cropChangeX) : (this.cropW = this.w - this.cropChangeX + Math.abs(t) > this.w ? this.cropChangeX : Math.abs(t), this.cropOffsertX = this.cropChangeX + t > 0 ? this.cropChangeX + t : 0), this.fixed) {
                        var o = this.cropW / this.fixedNumber[0] * this.fixedNumber[1];
                        o + this.cropOffsertY > this.h ? (this.cropH = this.h - this.cropOffsertY, this.cropW = this.cropH / this.fixedNumber[1] * this.fixedNumber[0], this.cropOffsertX = t > 0 ? this.cropChangeX : this.cropChangeX - this.cropW) : this.cropH = o, this.cropOffsertY = this.cropOffsertY
                    } else s > 0 ? (this.cropH = s + this.cropChangeY > this.h ? this.h - this.cropChangeY : s, this.cropOffsertY = this.cropChangeY) : (this.cropH = this.h - this.cropChangeY + Math.abs(s) > this.h ? this.cropChangeY : Math.abs(s), this.cropOffsertY = this.cropChangeY + s > 0 ? this.cropChangeY + s : 0)
                }))
            }, changeCropSize(t, e, i, s, o) {
                t.preventDefault(), window.addEventListener("mousemove", this.changeCropNow), window.addEventListener("mouseup", this.changeCropEnd), window.addEventListener("touchmove", this.changeCropNow), window.addEventListener("touchend", this.changeCropEnd), this.canChangeX = e, this.canChangeY = i, this.changeCropTypeX = s, this.changeCropTypeY = o, this.cropX = "clientX" in t ? t.clientX : t.touches[0].clientX, this.cropY = "clientY" in t ? t.clientY : t.touches[0].clientY, this.cropOldW = this.cropW, this.cropOldH = this.cropH, this.cropChangeX = this.cropOffsertX, this.cropChangeY = this.cropOffsertY, this.fixed && this.canChangeX && this.canChangeY && (this.canChangeY = 0), this.$emit("change-crop-size", {
                    width: this.cropW,
                    height: this.cropH
                })
            }, changeCropNow(t) {
                t.preventDefault();
                var e = "clientX" in t ? t.clientX : t.touches ? t.touches[0].clientX : 0,
                    i = "clientY" in t ? t.clientY : t.touches ? t.touches[0].clientY : 0;
                let s = this.w, o = this.h, r = 0, h = 0;
                if (this.centerBox) {
                    let t = this.getImgAxis(), e = t.x2, i = t.y2;
                    r = t.x1 > 0 ? t.x1 : 0, h = t.y1 > 0 ? t.y1 : 0, s > e && (s = e), o > i && (o = i)
                }
                this.$nextTick((() => {
                    var t = e - this.cropX, a = i - this.cropY;
                    if (this.canChangeX && (1 === this.changeCropTypeX ? this.cropOldW - t > 0 ? (this.cropW = s - this.cropChangeX - t <= s - r ? this.cropOldW - t : this.cropOldW + this.cropChangeX - r, this.cropOffsertX = s - this.cropChangeX - t <= s - r ? this.cropChangeX + t : r) : (this.cropW = Math.abs(t) + this.cropChangeX <= s ? Math.abs(t) - this.cropOldW : s - this.cropOldW - this.cropChangeX, this.cropOffsertX = this.cropChangeX + this.cropOldW) : 2 === this.changeCropTypeX && (this.cropOldW + t > 0 ? (this.cropW = this.cropOldW + t + this.cropOffsertX <= s ? this.cropOldW + t : s - this.cropOffsertX, this.cropOffsertX = this.cropChangeX) : (this.cropW = s - this.cropChangeX + Math.abs(t + this.cropOldW) <= s - r ? Math.abs(t + this.cropOldW) : this.cropChangeX - r, this.cropOffsertX = s - this.cropChangeX + Math.abs(t + this.cropOldW) <= s - r ? this.cropChangeX - Math.abs(t + this.cropOldW) : r))), this.canChangeY && (1 === this.changeCropTypeY ? this.cropOldH - a > 0 ? (this.cropH = o - this.cropChangeY - a <= o - h ? this.cropOldH - a : this.cropOldH + this.cropChangeY - h, this.cropOffsertY = o - this.cropChangeY - a <= o - h ? this.cropChangeY + a : h) : (this.cropH = Math.abs(a) + this.cropChangeY <= o ? Math.abs(a) - this.cropOldH : o - this.cropOldH - this.cropChangeY, this.cropOffsertY = this.cropChangeY + this.cropOldH) : 2 === this.changeCropTypeY && (this.cropOldH + a > 0 ? (this.cropH = this.cropOldH + a + this.cropOffsertY <= o ? this.cropOldH + a : o - this.cropOffsertY, this.cropOffsertY = this.cropChangeY) : (this.cropH = o - this.cropChangeY + Math.abs(a + this.cropOldH) <= o - h ? Math.abs(a + this.cropOldH) : this.cropChangeY - h, this.cropOffsertY = o - this.cropChangeY + Math.abs(a + this.cropOldH) <= o - h ? this.cropChangeY - Math.abs(a + this.cropOldH) : h))), this.canChangeX && this.fixed) {
                        var c = this.cropW / this.fixedNumber[0] * this.fixedNumber[1];
                        c + this.cropOffsertY > o ? (this.cropH = o - this.cropOffsertY, this.cropW = this.cropH / this.fixedNumber[1] * this.fixedNumber[0]) : this.cropH = c
                    }
                    if (this.canChangeY && this.fixed) {
                        var n = this.cropH / this.fixedNumber[1] * this.fixedNumber[0];
                        n + this.cropOffsertX > s ? (this.cropW = s - this.cropOffsertX, this.cropH = this.cropW / this.fixedNumber[0] * this.fixedNumber[1]) : this.cropW = n
                    }
                }))
            }, checkCropLimitSize() {
                let {cropW: t, cropH: e, limitMinSize: i} = this, s = new Array;
                return s = Array.isArray[i] ? i : [i, i], t = parseFloat(s[0]), e = parseFloat(s[1]), [t, e]
            }, changeCropEnd(t) {
                window.removeEventListener("mousemove", this.changeCropNow), window.removeEventListener("mouseup", this.changeCropEnd), window.removeEventListener("touchmove", this.changeCropNow), window.removeEventListener("touchend", this.changeCropEnd)
            }, endCrop() {
                0 === this.cropW && 0 === this.cropH && (this.cropping = !1), window.removeEventListener("mousemove", this.createCrop), window.removeEventListener("mouseup", this.endCrop), window.removeEventListener("touchmove", this.createCrop), window.removeEventListener("touchend", this.endCrop)
            }, startCrop() {
                this.crop = !0
            }, stopCrop() {
                this.crop = !1
            }, clearCrop() {
                this.cropping = !1, this.cropW = 0, this.cropH = 0
            }, cropMove(t) {
                if (t.preventDefault(), !this.canMoveBox) return this.crop = !1, this.startMove(t), !1;
                if (t.touches && 2 === t.touches.length) return this.crop = !1, this.startMove(t), this.leaveCrop(), !1;
                window.addEventListener("mousemove", this.moveCrop), window.addEventListener("mouseup", this.leaveCrop), window.addEventListener("touchmove", this.moveCrop), window.addEventListener("touchend", this.leaveCrop);
                let e, i, s = "clientX" in t ? t.clientX : t.touches[0].clientX,
                    o = "clientY" in t ? t.clientY : t.touches[0].clientY;
                e = s - this.cropOffsertX, i = o - this.cropOffsertY, this.cropX = e, this.cropY = i, this.$emit("cropMoving", {
                    moving: !0,
                    axis: this.getCropAxis()
                }), this.$emit("crop-moving", {moving: !0, axis: this.getCropAxis()})
            }, moveCrop(t, e) {
                let i = 0, s = 0;
                t && (t.preventDefault(), i = "clientX" in t ? t.clientX : t.touches[0].clientX, s = "clientY" in t ? t.clientY : t.touches[0].clientY), this.$nextTick((() => {
                    let t, o, r = i - this.cropX, h = s - this.cropY;
                    if (e && (r = this.cropOffsertX, h = this.cropOffsertY), t = r <= 0 ? 0 : r + this.cropW > this.w ? this.w - this.cropW : r, o = h <= 0 ? 0 : h + this.cropH > this.h ? this.h - this.cropH : h, this.centerBox) {
                        let e = this.getImgAxis();
                        t <= e.x1 && (t = e.x1), t + this.cropW > e.x2 && (t = e.x2 - this.cropW), o <= e.y1 && (o = e.y1), o + this.cropH > e.y2 && (o = e.y2 - this.cropH)
                    }
                    this.cropOffsertX = t, this.cropOffsertY = o, this.$emit("cropMoving", {
                        moving: !0,
                        axis: this.getCropAxis()
                    }), this.$emit("crop-moving", {moving: !0, axis: this.getCropAxis()})
                }))
            }, getImgAxis(t, e, i) {
                t = t || this.x, e = e || this.y, i = i || this.scale;
                let s = {x1: 0, x2: 0, y1: 0, y2: 0}, o = this.trueWidth * i, r = this.trueHeight * i;
                switch (this.rotate) {
                    case 0:
                        s.x1 = t + this.trueWidth * (1 - i) / 2, s.x2 = s.x1 + this.trueWidth * i, s.y1 = e + this.trueHeight * (1 - i) / 2, s.y2 = s.y1 + this.trueHeight * i;
                        break;
                    case 1:
                    case-1:
                    case 3:
                    case-3:
                        s.x1 = t + this.trueWidth * (1 - i) / 2 + (o - r) / 2, s.x2 = s.x1 + this.trueHeight * i, s.y1 = e + this.trueHeight * (1 - i) / 2 + (r - o) / 2, s.y2 = s.y1 + this.trueWidth * i;
                        break;
                    default:
                        s.x1 = t + this.trueWidth * (1 - i) / 2, s.x2 = s.x1 + this.trueWidth * i, s.y1 = e + this.trueHeight * (1 - i) / 2, s.y2 = s.y1 + this.trueHeight * i
                }
                return s
            }, getCropAxis() {
                let t = {x1: 0, x2: 0, y1: 0, y2: 0};
                return t.x1 = this.cropOffsertX, t.x2 = t.x1 + this.cropW, t.y1 = this.cropOffsertY, t.y2 = t.y1 + this.cropH, t
            }, leaveCrop(t) {
                window.removeEventListener("mousemove", this.moveCrop), window.removeEventListener("mouseup", this.leaveCrop), window.removeEventListener("touchmove", this.moveCrop), window.removeEventListener("touchend", this.leaveCrop), this.$emit("cropMoving", {
                    moving: !1,
                    axis: this.getCropAxis()
                }), this.$emit("crop-moving", {moving: !1, axis: this.getCropAxis()})
            }, getCropChecked(t) {
                let e = document.createElement("canvas"), i = new Image, s = this.rotate, o = this.trueWidth,
                    r = this.trueHeight, h = this.cropOffsertX, a = this.cropOffsertY;

                function c(t, i) {
                    e.width = Math.round(t), e.height = Math.round(i)
                }

                i.onload = () => {
                    if (0 !== this.cropW) {
                        let t = e.getContext("2d"), n = 1;
                        this.high & !this.full && (n = window.devicePixelRatio), 1 !== this.enlarge & !this.full && (n = Math.abs(Number(this.enlarge)));
                        let p = this.cropW * n, l = this.cropH * n, d = o * this.scale * n, u = r * this.scale * n,
                            g = (this.x - h + this.trueWidth * (1 - this.scale) / 2) * n,
                            m = (this.y - a + this.trueHeight * (1 - this.scale) / 2) * n;
                        switch (c(p, l), t.save(), s) {
                            case 0:
                                this.full ? (c(p / this.scale, l / this.scale), t.drawImage(i, g / this.scale, m / this.scale, d / this.scale, u / this.scale)) : t.drawImage(i, g, m, d, u);
                                break;
                            case 1:
                            case-3:
                                this.full ? (c(p / this.scale, l / this.scale), g = g / this.scale + (d / this.scale - u / this.scale) / 2, m = m / this.scale + (u / this.scale - d / this.scale) / 2, t.rotate(90 * s * Math.PI / 180), t.drawImage(i, m, -g - u / this.scale, d / this.scale, u / this.scale)) : (g += (d - u) / 2, m += (u - d) / 2, t.rotate(90 * s * Math.PI / 180), t.drawImage(i, m, -g - u, d, u));
                                break;
                            case 2:
                            case-2:
                                this.full ? (c(p / this.scale, l / this.scale), t.rotate(90 * s * Math.PI / 180), g /= this.scale, m /= this.scale, t.drawImage(i, -g - d / this.scale, -m - u / this.scale, d / this.scale, u / this.scale)) : (t.rotate(90 * s * Math.PI / 180), t.drawImage(i, -g - d, -m - u, d, u));
                                break;
                            case 3:
                            case-1:
                                this.full ? (c(p / this.scale, l / this.scale), g = g / this.scale + (d / this.scale - u / this.scale) / 2, m = m / this.scale + (u / this.scale - d / this.scale) / 2, t.rotate(90 * s * Math.PI / 180), t.drawImage(i, -m - d / this.scale, g, d / this.scale, u / this.scale)) : (g += (d - u) / 2, m += (u - d) / 2, t.rotate(90 * s * Math.PI / 180), t.drawImage(i, -m - d, g, d, u));
                                break;
                            default:
                                this.full ? (c(p / this.scale, l / this.scale), t.drawImage(i, g / this.scale, m / this.scale, d / this.scale, u / this.scale)) : t.drawImage(i, g, m, d, u)
                        }
                        t.restore()
                    } else {
                        let t = o * this.scale, h = r * this.scale, a = e.getContext("2d");
                        switch (a.save(), s) {
                            case 0:
                                c(t, h), a.drawImage(i, 0, 0, t, h);
                                break;
                            case 1:
                            case-3:
                                c(h, t), a.rotate(90 * s * Math.PI / 180), a.drawImage(i, 0, -h, t, h);
                                break;
                            case 2:
                            case-2:
                                c(t, h), a.rotate(90 * s * Math.PI / 180), a.drawImage(i, -t, -h, t, h);
                                break;
                            case 3:
                            case-1:
                                c(h, t), a.rotate(90 * s * Math.PI / 180), a.drawImage(i, -t, 0, t, h);
                                break;
                            default:
                                c(t, h), a.drawImage(i, 0, 0, t, h)
                        }
                        a.restore()
                    }
                    t(e)
                }, "data" !== this.img.substr(0, 4) && (i.crossOrigin = "Anonymous"), i.src = this.imgs
            }, getCropData(t) {
                this.getCropChecked((e => {
                    t(e.toDataURL("image/" + this.outputType, this.outputSize))
                }))
            }, getCropBlob(t) {
                this.getCropChecked((e => {
                    e.toBlob((e => t(e)), "image/" + this.outputType, this.outputSize)
                }))
            }, showPreview() {
                if (!this.isCanShow) return !1;
                this.isCanShow = !1, setTimeout((() => {
                    this.isCanShow = !0
                }), 16);
                let t = this.cropW, e = this.cropH, i = this.scale;
                var s = {};
                s.div = {width: `${t}px`, height: `${e}px`};
                let o = (this.x - this.cropOffsertX) / i, r = (this.y - this.cropOffsertY) / i;
                s.w = t, s.h = e, s.url = this.imgs, s.img = {
                    width: `${this.trueWidth}px`,
                    height: `${this.trueHeight}px`,
                    transform: `scale(${i})translate3d(${o}px, ${r}px, 0px)rotateZ(${90 * this.rotate}deg)`
                }, s.html = `\n      <div class="show-preview" style="width: ${s.w}px; height: ${s.h}px,; overflow: hidden">\n        <div style="width: ${t}px; height: ${e}px">\n          <img src=${s.url} style="width: ${this.trueWidth}px; height: ${this.trueHeight}px; transform:\n          scale(${i})translate3d(${o}px, ${r}px, 0px)rotateZ(${90 * this.rotate}deg)">\n        </div>\n      </div>`, this.$emit("realTime", s), this.$emit("real-time", s)
            }, reload() {
                let t = new Image;
                t.onload = () => {
                    this.w = parseFloat(window.getComputedStyle(this.$refs.cropper).width), this.h = parseFloat(window.getComputedStyle(this.$refs.cropper).height), this.trueWidth = t.width, this.trueHeight = t.height, this.original ? this.scale = 1 : this.scale = this.checkedMode(), this.$nextTick((() => {
                        this.x = -(this.trueWidth - this.trueWidth * this.scale) / 2 + (this.w - this.trueWidth * this.scale) / 2, this.y = -(this.trueHeight - this.trueHeight * this.scale) / 2 + (this.h - this.trueHeight * this.scale) / 2, this.loading = !1, this.autoCrop && this.goAutoCrop(), this.$emit("img-load", "success"), this.$emit("imgLoad", "success"), setTimeout((() => {
                            this.showPreview()
                        }), 20)
                    }))
                }, t.onerror = () => {
                    this.$emit("imgLoad", "error"), this.$emit("img-load", "error")
                }, t.src = this.imgs
            }, checkedMode() {
                let t = 1, e = this.trueWidth, i = this.trueHeight;
                const s = this.mode.split(" ");
                switch (s[0]) {
                    case"contain":
                        this.trueWidth > this.w && (t = this.w / this.trueWidth), this.trueHeight * t > this.h && (t = this.h / this.trueHeight);
                        break;
                    case"cover":
                        e = this.w, t = e / this.trueWidth, i *= t, i < this.h && (i = this.h, t = i / this.trueHeight);
                        break;
                    default:
                        try {
                            let o = s[0];
                            if (-1 !== o.search("px")) {
                                o = o.replace("px", ""), e = parseFloat(o);
                                const r = e / this.trueWidth;
                                let h = 1, a = s[1];
                                -1 !== a.search("px") && (a = a.replace("px", ""), i = parseFloat(a), h = i / this.trueHeight), t = Math.min(r, h)
                            }
                            if (-1 !== o.search("%") && (o = o.replace("%", ""), e = parseFloat(o) / 100 * this.w, t = e / this.trueWidth), 2 === s.length && "auto" === o) {
                                let e = s[1];
                                -1 !== e.search("px") && (e = e.replace("px", ""), i = parseFloat(e), t = i / this.trueHeight), -1 !== e.search("%") && (e = e.replace("%", ""), i = parseFloat(e) / 100 * this.h, t = i / this.trueHeight)
                            }
                        } catch (o) {
                            t = 1
                        }
                }
                return t
            }, goAutoCrop(t, e) {
                if ("" === this.imgs || null === this.imgs) return;
                this.clearCrop(), this.cropping = !0;
                let i = this.w, s = this.h;
                if (this.centerBox) {
                    const t = Math.abs(this.rotate) % 2 > 0;
                    let e = (t ? this.trueHeight : this.trueWidth) * this.scale,
                        o = (t ? this.trueWidth : this.trueHeight) * this.scale;
                    i = e < i ? e : i, s = o < s ? o : s
                }
                var o = t || parseFloat(this.autoCropWidth), r = e || parseFloat(this.autoCropHeight);
                0 !== o && 0 !== r || (o = .8 * i, r = .8 * s), o = o > i ? i : o, r = r > s ? s : r, this.fixed && (r = o / this.fixedNumber[0] * this.fixedNumber[1]), r > this.h && (o = (r = this.h) / this.fixedNumber[1] * this.fixedNumber[0]), this.changeCrop(o, r)
            }, changeCrop(t, e) {

                if (this.centerBox) {
                    let i = this.getImgAxis();
                    t > i.x2 - i.x1 && (e = (t = i.x2 - i.x1) / this.fixedNumber[0] * this.fixedNumber[1]), e > i.y2 - i.y1 && (t = (e = i.y2 - i.y1) / this.fixedNumber[1] * this.fixedNumber[0])
                }
                this.cropW = t, this.cropH = e, this.checkCropLimitSize(), this.$nextTick((() => {
                    if(this.centerBox){
                        this.cropOffsertX =(this.w - this.cropW) / 2;
                        this.cropOffsertY = (this.h - this.cropH) / 2;
                        this.centerBox && this.moveCrop(null, !0)
                    }else{
                        this.cropOffsertX = this.autoCropX;
                        this.cropOffsertY = this.autoCropY;

                    }


                }))
            }, refresh() {
                this.img, this.imgs = "", this.scale = 1, this.crop = !1, this.rotate = 0, this.w = 0, this.h = 0, this.trueWidth = 0, this.trueHeight = 0, this.clearCrop(), this.$nextTick((() => {
                    this.checkedImg()
                }))
            }, rotateLeft() {
                this.rotate = this.rotate <= -3 ? 0 : this.rotate - 1
            }, rotateRight() {
                this.rotate = this.rotate >= 3 ? 0 : this.rotate + 1
            }, rotateClear() {
                this.rotate = 0
            }, checkoutImgAxis(t, e, i) {
                t = t || this.x, e = e || this.y, i = i || this.scale;
                let s = !0;
                if (this.centerBox) {
                    let o = this.getImgAxis(t, e, i), r = this.getCropAxis();
                    o.x1 >= r.x1 && (s = !1), o.x2 <= r.x2 && (s = !1), o.y1 >= r.y1 && (s = !1), o.y2 <= r.y2 && (s = !1)
                }
                return s
            }
        },
        mounted() {
            this.support = "onwheel" in document.createElement("div") ? "wheel" : void 0 !== document.onmousewheel ? "mousewheel" : "DOMMouseScroll";
            let t = this;
            var e = navigator.userAgent;
            this.isIOS = !!e.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/), HTMLCanvasElement.prototype.toBlob || Object.defineProperty(HTMLCanvasElement.prototype, "toBlob", {
                value: function (e, i, s) {
                    for (var o = atob(this.toDataURL(i, s).split(",")[1]), r = o.length, h = new Uint8Array(r), a = 0; a < r; a++) h[a] = o.charCodeAt(a);
                    e(new Blob([h], {type: t.type || "image/png"}))
                }
            }), this.showPreview(), this.checkedImg()
        },
        destroyed() {
            window.removeEventListener("mousemove", this.moveCrop), window.removeEventListener("mouseup", this.leaveCrop), window.removeEventListener("touchmove", this.moveCrop), window.removeEventListener("touchend", this.leaveCrop), this.cancelScale()
        }
    });
    e.pushScopeId("data-v-48aab112");
    const o = {key: 0, class: "cropper-box"}, r = ["src"], h = {class: "cropper-view-box"}, a = ["src"], c = {key: 1};
    e.popScopeId(), s.render = function (t, i, s, n, p, l) {
        return e.openBlock(), e.createElementBlock("div", {
            class: "vue-cropper",
            ref: "cropper",
            onMouseover: i[28] || (i[28] = (...e) => t.scaleImg && t.scaleImg(...e)),
            onMouseout: i[29] || (i[29] = (...e) => t.cancelScale && t.cancelScale(...e))
        }, [t.imgs ? (e.openBlock(), e.createElementBlock("div", o, [e.withDirectives(e.createElementVNode("div", {
            class: "cropper-box-canvas",
            style: e.normalizeStyle({
                width: t.trueWidth + "px",
                height: t.trueHeight + "px",
                transform: "scale(" + t.scale + "," + t.scale + ") translate3d(" + t.x / t.scale + "px," + t.y / t.scale + "px,0)rotateZ(" + 90 * t.rotate + "deg)"
            })
        }, [e.createElementVNode("img", {
            src: t.imgs,
            alt: "cropper-img",
            ref: "cropperImg"
        }, null, 8, r)], 4), [[e.vShow, !t.loading]])])) : e.createCommentVNode("", !0), e.createElementVNode("div", {
            class: e.normalizeClass(["cropper-drag-box", {
                "cropper-move": t.move && !t.crop,
                "cropper-crop": t.crop,
                "cropper-modal": t.cropping
            }]),
            onMousedown: i[0] || (i[0] = (...e) => t.startMove && t.startMove(...e)),
            onTouchstart: i[1] || (i[1] = (...e) => t.startMove && t.startMove(...e))
        }, null, 34), e.withDirectives(e.createElementVNode("div", {
            class: "cropper-crop-box",
            style: e.normalizeStyle({
                width: t.cropW + "px",
                height: t.cropH + "px",
                transform: "translate3d(" + t.cropOffsertX + "px," + t.cropOffsertY + "px,0)"
            })
        }, [e.createElementVNode("span", h, [e.createElementVNode("img", {
            style: e.normalizeStyle({
                width: t.trueWidth + "px",
                height: t.trueHeight + "px",
                transform: "scale(" + t.scale + "," + t.scale + ") translate3d(" + (t.x - t.cropOffsertX) / t.scale + "px," + (t.y - t.cropOffsertY) / t.scale + "px,0)rotateZ(" + 90 * t.rotate + "deg)"
            }), src: t.imgs, alt: "cropper-img"
        }, null, 12, a)]), e.createElementVNode("span", {
            class: "cropper-face cropper-move",
            onMousedown: i[2] || (i[2] = (...e) => t.cropMove && t.cropMove(...e)),
            onTouchstart: i[3] || (i[3] = (...e) => t.cropMove && t.cropMove(...e))
        }, null, 32), t.info ? (e.openBlock(), e.createElementBlock("span", {
            key: 0,
            class: "crop-info",
            style: e.normalizeStyle({top: t.cropInfo.top})
        }, e.toDisplayString(t.cropInfo.width) + " × " + e.toDisplayString(t.cropInfo.height), 5)) : e.createCommentVNode("", !0), t.fixedBox ? e.createCommentVNode("", !0) : (e.openBlock(), e.createElementBlock("span", c, [e.createElementVNode("span", {
            class: "crop-line line-w",
            onMousedown: i[4] || (i[4] = e => t.changeCropSize(e, !1, !0, 0, 1)),
            onTouchstart: i[5] || (i[5] = e => t.changeCropSize(e, !1, !0, 0, 1))
        }, null, 32), e.createElementVNode("span", {
            class: "crop-line line-a",
            onMousedown: i[6] || (i[6] = e => t.changeCropSize(e, !0, !1, 1, 0)),
            onTouchstart: i[7] || (i[7] = e => t.changeCropSize(e, !0, !1, 1, 0))
        }, null, 32), e.createElementVNode("span", {
            class: "crop-line line-s",
            onMousedown: i[8] || (i[8] = e => t.changeCropSize(e, !1, !0, 0, 2)),
            onTouchstart: i[9] || (i[9] = e => t.changeCropSize(e, !1, !0, 0, 2))
        }, null, 32), e.createElementVNode("span", {
            class: "crop-line line-d",
            onMousedown: i[10] || (i[10] = e => t.changeCropSize(e, !0, !1, 2, 0)),
            onTouchstart: i[11] || (i[11] = e => t.changeCropSize(e, !0, !1, 2, 0))
        }, null, 32), e.createElementVNode("span", {
            class: "crop-point point1",
            onMousedown: i[12] || (i[12] = e => t.changeCropSize(e, !0, !0, 1, 1)),
            onTouchstart: i[13] || (i[13] = e => t.changeCropSize(e, !0, !0, 1, 1))
        }, null, 32), e.createElementVNode("span", {
            class: "crop-point point3",
            onMousedown: i[16] || (i[16] = e => t.changeCropSize(e, !0, !0, 2, 1)),
            onTouchstart: i[17] || (i[17] = e => t.changeCropSize(e, !0, !0, 2, 1))
        }, null, 32), e.createElementVNode("span", {
            class: "crop-point point6",
            onMousedown: i[22] || (i[22] = e => t.changeCropSize(e, !0, !0, 1, 2)),
            onTouchstart: i[23] || (i[23] = e => t.changeCropSize(e, !0, !0, 1, 2))
        }, null, 32), e.createElementVNode("span", {
            class: "crop-point point8",
            onMousedown: i[26] || (i[26] = e => t.changeCropSize(e, !0, !0, 2, 2)),
            onTouchstart: i[27] || (i[27] = e => t.changeCropSize(e, !0, !0, 2, 2))
        }, null, 32)]))], 4), [[e.vShow, t.cropping]])], 544)
    }, s.__scopeId = "data-v-48aab112";
    "undefined" != typeof window && window.Vue && window.Vue.createApp({}).component("VueCropper", s);
    const n = {
        version: "1.0.2", install: function (t) {
            t.component("VueCropper", s)
        }, VueCropper: s
    };
    t.VueCropper = s, t.default = n, t.globalCropper = n, Object.defineProperty(t, "__esModule", {value: !0}), t[Symbol.toStringTag] = "Module"
}));