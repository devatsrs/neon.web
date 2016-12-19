
function getDateFormat(e) {
    var t = jQuery("html").data("dateFormat");
    return DATE_FORMATS[t][e]
}

function closeableFlash(e) {
    e = jQuery(e), jQuery("<a />").addClass("close").attr("href", "#").appendTo(e).click(function() {
        return e.fadeOut(600), !1
    }), setTimeout(function() {
        "none" != e.css("display") && e.hide("blind", {}, 500)
    }, 2e4), setTimeout(function() {
        e.find("a").remove(), delete e.find("a"), delete e.prevObject
    }, 20700)
}

function setRecentCallDuration() {
    "use strict";
    jQuery(".recent_calls_call_duration").each(function() {
        var e = jQuery(this).data("time");
        void 0 !== e && "number" == typeof e && jQuery(this).html(e.toTime())
    })
}! function(e) {
    "use strict";

    function t(e, t, i) {
        return "function" == typeof e ? e.apply(t, i) : e
    }
    var i;
    e(document).ready(function() {
        e.support.transition = function() {
            var e = document.body || document.documentElement,
                t = e.style,
                i = void 0 !== t.transition || void 0 !== t.WebkitTransition || void 0 !== t.MozTransition || void 0 !== t.MsTransition || void 0 !== t.OTransition;
            return i
        }(), e.support.transition && (i = "TransitionEnd", e.browser.webkit ? i = "webkitTransitionEnd" : e.browser.mozilla ? i = "transitionend" : e.browser.opera && (i = "oTransitionEnd"))
    });
    var n = function(t, i) {
        this.$element = e(t), this.options = i, this.enabled = !0, this.fixTitle()
    };
    n.prototype = {
        show: function() {
            var i, n, r, s, a, o;
            if (this.hasContent() && this.enabled) {
                switch (a = this.tip(), this.setContent(), this.options.animate && a.addClass("fade"), a.css({
                    top: 0,
                    left: 0,
                    display: "block"
                }).prependTo(document.body), i = e.extend({}, this.$element.offset(), {
                    width: this.$element[0].offsetWidth,
                    height: this.$element[0].offsetHeight
                }), n = a[0].offsetWidth, r = a[0].offsetHeight, s = t(this.options.placement, this, [a[0], this.$element[0]])) {
                    case "below":
                        o = {
                            top: i.top + i.height + this.options.offset,
                            left: i.left + i.width / 2 - n / 2
                        };
                        break;
                    case "above":
                        o = {
                            top: i.top - r - this.options.offset,
                            left: i.left + i.width / 2 - n / 2
                        };
                        break;
                    case "left":
                        o = {
                            top: i.top + i.height / 2 - r / 2,
                            left: i.left - n - this.options.offset
                        };
                        break;
                    case "topLeft":
                        o = {
                            top: i.top - (i.height + 10),
                            left: i.left - n - this.options.offset
                        };
                        break;
                    case "right":
                        o = {
                            top: i.top + i.height / 2 - r / 2,
                            left: i.left + i.width + this.options.offset
                        };
                        break;
                    case "topRight":
                        o = {
                            top: i.top - (i.height + 10),
                            left: i.left + this.options.offset
                        };
                        break;
                    case "belowLeft":
                        o = {
                            top: i.top + i.height + this.options.offset,
                            left: i.left + i.width - n + this.options.offset
                        };
                        break;
                    case "belowRight":
                        o = {
                            top: i.top + i.height + this.options.offset,
                            left: i.left + i.width / 2 - n / 5 + this.options.offset
                        };
                        break;
                    case "partialLeft":
                        o = {
                            top: i.top + i.height + this.options.offset + i.height / 3,
                            left: i.left + i.width - 4 * (n / 4) + this.options.offset
                        };
                        break;
                    case "bottomLeft":
                        o = {
                            top: i.top + 2 * i.height + this.options.offset - 5,
                            left: i.left + i.width - 2.88 * (n / 3) + this.options.offset
                        }
                }
                a.css(o).addClass(s).addClass("in")
            }
        },
        setContent: function() {
            var e = this.tip();
            e.find(".twipsy-inner")[this.options.html ? "html" : "text"](this.getTitle()), e[0].className = "twipsy", this.$element.attr("twipsy-content-set", !0)
        },
        hide: function() {
            var e = this.tip();
            e.removeClass("in"), e.css({
                display: "none"
            })
        },
        fixTitle: function() {
            var e = this.$element;
            (e.attr("title") || "string" != typeof e.attr("data-original-title")) && e.attr("data-original-title", e.attr("title") || "").removeAttr("title")
        },
        hasContent: function() {
            return this.getTitle()
        },
        getTitle: function() {
            var e, t = this.$element,
                i = this.options;
            return this.fixTitle(), "string" == typeof i.title ? e = t.attr("title" == i.title ? "data-original-title" : i.title) : "function" == typeof i.title && (e = i.title.call(t[0])), e = ("" + e).replace(/(^\s*|\s*$)/, ""), e || i.fallback
        },
        tip: function() {
            return this.$tip = this.$tip || e('<div class="twipsy" />').html(this.options.template)
        },
        validate: function() {
            this.$element[0].parentNode || (this.hide(), this.$element = null, this.options = null)
        },
        enable: function() {
            this.enabled = !0
        },
        disable: function() {
            this.enabled = !1
        },
        toggleEnabled: function() {
            this.enabled = !this.enabled
        },
        toggle: function() {
            this[this.tip().hasClass("in") ? "hide" : "show"]()
        }
    }, e.fn.twipsy = function(t) {
        return e.fn.twipsy.initWith.call(this, t, n, "twipsy"), this
    }, e.fn.twipsy.initWith = function(t, i, n) {
        function r(r) {
            var s = e.data(r, n);
            return s || (s = new i(r, e.fn.twipsy.elementOptions(r, t)), e.data(r, n, s)), s
        }

        function s() {
            var e = r(this);
            e.hoverState = "in", 0 == t.delayIn ? e.show() : (e.fixTitle(), setTimeout(function() {
                "in" == e.hoverState && e.show()
            }, t.delayIn))
        }

        function a() {
            var e = r(this);
            e.hoverState = "out", 0 == t.delayOut ? e.hide() : setTimeout(function() {
                "out" == e.hoverState && e.hide()
            }, t.delayOut)
        }
        var o, l, c, d;
        return t === !0 ? this.data(n) : "string" == typeof t ? (o = this.data(n), o && o[t](), this) : (t = e.extend({}, e.fn[n].defaults, t), t.live || this.each(function() {
            r(this)
        }), "manual" != t.trigger && (l = t.live ? "on" : "bind", c = "hover" == t.trigger ? "mouseenter" : "focus", d = "hover" == t.trigger ? "mouseleave" : "blur", this[l](c, s)[l](d, a)), this)
    }, e.fn.twipsy.Twipsy = n, e.fn.twipsy.defaults = {
        animate: !0,
        delayIn: 0,
        delayOut: 0,
        fallback: "",
        placement: "above",
        html: !1,
        live: !1,
        offset: 0,
        title: "title",
        trigger: "hover",
        template: '<div class="twipsy-arrow"></div><div class="twipsy-inner"></div>'
    }, e.fn.twipsy.rejectAttrOptions = ["title"], e.fn.twipsy.elementOptions = function(t, i) {
        for (var n = e(t).data(), r = e.fn.twipsy.rejectAttrOptions, s = r.length; s--;) delete n[r[s]];
        if ("rtl" == e("html").attr("dir")) switch (n.placement) {
            case "right":
                n.placement = "left";
                break;
            case "topRight":
                n.placement = "topLeft";
                break;
            case "belowRight":
                n.placement = "belowLeft";
                break;
            case "left":
                n.placement = "right";
                break;
            case "topLeft":
                n.placement = "topRight";
                break;
            case "belowLeft":
                n.placement = "belowRight"
        }
        return e.extend({}, i, n)
    }
}(window.jQuery || window.ender),
/**
 * Copyright (c) 2011-2013 Felix Gnass
 * Licensed under the MIT license
 */
function(e, t) {
    "object" == typeof exports ? module.exports = t() : "function" == typeof define && define.amd ? define(t) : e.Spinner = t()
}(this, function() {
    "use strict";

    function e(e, t) {
        var i, n = document.createElement(e || "div");
        for (i in t) n[i] = t[i];
        return n
    }

    function t(e) {
        for (var t = 1, i = arguments.length; i > t; t++) e.appendChild(arguments[t]);
        return e
    }

    function i(e, t, i, n) {
        var r = ["opacity", t, ~~(100 * e), i, n].join("-"),
            s = .01 + 100 * (i / n),
            a = Math.max(1 - (1 - e) / t * (100 - s), e),
            o = c.substring(0, c.indexOf("Animation")).toLowerCase(),
            l = o && "-" + o + "-" || "";
        return u[r] || (h.insertRule("@" + l + "keyframes " + r + "{" + "0%{opacity:" + a + "}" + s + "%{opacity:" + e + "}" + (s + .01) + "%{opacity:1}" + (s + t) % 100 + "%{opacity:" + e + "}" + "100%{opacity:" + a + "}" + "}", h.cssRules.length), u[r] = 1), r
    }

    function n(e, t) {
        var i, n, r = e.style;
        if (void 0 !== r[t]) return t;
        for (t = t.charAt(0).toUpperCase() + t.slice(1), n = 0; n < d.length; n++)
            if (i = d[n] + t, void 0 !== r[i]) return i
    }

    function r(e, t) {
        for (var i in t) e.style[n(e, i) || i] = t[i];
        return e
    }

    function s(e) {
        for (var t = 1; t < arguments.length; t++) {
            var i = arguments[t];
            for (var n in i) void 0 === e[n] && (e[n] = i[n])
        }
        return e
    }

    function a(e) {
        for (var t = {
                x: e.offsetLeft,
                y: e.offsetTop
            }; e = e.offsetParent;) t.x += e.offsetLeft, t.y += e.offsetTop;
        return t
    }

    function o(e) {
        return "undefined" == typeof this ? new o(e) : (this.opts = s(e || {}, o.defaults, p), void 0)
    }

    function l() {
        function i(t, i) {
            return e("<" + t + ' xmlns="urn:schemas-microsoft.com:vml" class="spin-vml">', i)
        }
        h.addRule(".spin-vml", "behavior:url(#default#VML)"), o.prototype.lines = function(e, n) {
            function s() {
                return r(i("group", {
                    coordsize: c + " " + c,
                    coordorigin: -l + " " + -l
                }), {
                    width: c,
                    height: c
                })
            }

            function a(e, a, o) {
                t(u, t(r(s(), {
                    rotation: 360 / n.lines * e + "deg",
                    left: ~~a
                }), t(r(i("roundrect", {
                    arcsize: n.corners
                }), {
                    width: l,
                    height: n.width,
                    left: n.radius,
                    top: -n.width >> 1,
                    filter: o
                }), i("fill", {
                    color: n.color,
                    opacity: n.opacity
                }), i("stroke", {
                    opacity: 0
                }))))
            }
            var o, l = n.length + n.width,
                c = 2 * l,
                d = 2 * -(n.width + n.length) + "px",
                u = r(s(), {
                    position: "absolute",
                    top: d,
                    left: d
                });
            if (n.shadow)
                for (o = 1; o <= n.lines; o++) a(o, -2, "progid:DXImageTransform.Microsoft.Blur(pixelradius=2,makeshadow=1,shadowopacity=.3)");
            for (o = 1; o <= n.lines; o++) a(o);
            return t(e, u)
        }, o.prototype.opacity = function(e, t, i, n) {
            var r = e.firstChild;
            n = n.shadow && n.lines || 0, r && t + n < r.childNodes.length && (r = r.childNodes[t + n], r = r && r.firstChild, r = r && r.firstChild, r && (r.opacity = i))
        }
    }
    var c, d = ["webkit", "Moz", "ms", "O"],
        u = {},
        h = function() {
            var i = e("style", {
                type: "text/css"
            });
            return t(document.getElementsByTagName("head")[0], i), i.sheet || i.styleSheet
        }(),
        p = {
            lines: 12,
            length: 7,
            width: 5,
            radius: 10,
            rotate: 0,
            corners: 1,
            color: "#000",
            direction: 1,
            speed: 1,
            trail: 100,
            opacity: .25,
            fps: 20,
            zIndex: 2e9,
            className: "spinner",
            top: "auto",
            left: "auto",
            position: "relative"
        };
    o.defaults = {}, s(o.prototype, {
        spin: function(t) {
            this.stop();
            var i, n, s = this,
                o = s.opts,
                l = s.el = r(e(0, {
                    className: o.className
                }), {
                    position: o.position,
                    width: 0,
                    zIndex: o.zIndex
                }),
                d = o.radius + o.length + o.width;
            if (t && (t.insertBefore(l, t.firstChild || null), n = a(t), i = a(l), r(l, {
                    left: ("right" == o.left ? t.offsetWidth - 2 * d : "auto" == o.left ? n.x - i.x + (t.offsetWidth >> 1) : parseInt(o.left, 10) + d) + "px",
                    top: ("auto" == o.top ? n.y - i.y + (t.offsetHeight >> 1) : parseInt(o.top, 10) + d) + "px"
                })), l.setAttribute("role", "progressbar"), s.lines(l, s.opts), !c) {
                var u, h = 0,
                    p = (o.lines - 1) * (1 - o.direction) / 2,
                    f = o.fps,
                    m = f / o.speed,
                    g = (1 - o.opacity) / (m * o.trail / 100),
                    _ = m / o.lines;
                ! function v() {
                    h++;
                    for (var e = 0; e < o.lines; e++) u = Math.max(1 - (h + (o.lines - e) * _) % m * g, o.opacity), s.opacity(l, e * o.direction + p, u, o);
                    s.timeout = s.el && setTimeout(v, ~~(1e3 / f))
                }()
            }
            return s
        },
        stop: function() {
            var e = this.el;
            return e && (clearTimeout(this.timeout), e.parentNode && e.parentNode.removeChild(e), this.el = void 0), this
        },
        lines: function(n, s) {
            function a(t, i) {
                return r(e(), {
                    position: "absolute",
                    width: s.length + s.width + "px",
                    height: s.width + "px",
                    background: t,
                    boxShadow: i,
                    transformOrigin: "left",
                    transform: "rotate(" + ~~(360 / s.lines * l + s.rotate) + "deg) translate(" + s.radius + "px" + ",0)",
                    borderRadius: (s.corners * s.width >> 1) + "px"
                })
            }
            for (var o, l = 0, d = (s.lines - 1) * (1 - s.direction) / 2; l < s.lines; l++) o = r(e(), {
                position: "absolute",
                top: 1 + ~(s.width / 2) + "px",
                transform: s.hwaccel ? "translate3d(0,0,0)" : "",
                opacity: s.opacity,
                animation: c && i(s.opacity, s.trail, d + l * s.direction, s.lines) + " " + 1 / s.speed + "s linear infinite"
            }), s.shadow && t(o, r(a("#000", "0 0 4px #000"), {
                top: "2px"
            })), t(n, t(o, a(s.color, "0 0 1px rgba(0,0,0,.1)")));
            return n
        },
        opacity: function(e, t, i) {
            t < e.childNodes.length && (e.childNodes[t].style.opacity = i)
        }
    });
    var f = r(e("group"), {
        behavior: "url(#default#VML)"
    });
    return !n(f, "transform") && f.adj ? l() : c = n(f, "animation"), o
}),
/**
 * Copyright (c) 2011-2013 Felix Gnass
 * Licensed under the MIT license
 */
function(e) {
    if ("object" == typeof exports) e(require("jquery"), require("spin"));
    else if ("function" == typeof define && define.amd) define(["jquery", "spin"], e);
    else {
        if (!window.Spinner) throw new Error("spin.js not present");
        e(window.jQuery, window.Spinner)
    }
}(function(e, t) {
    e.fn.spin = function(i, n) {
        return this.each(function() {
            var r = e(this),
                s = r.data();
            s.spinner && (s.spinner.stop(), delete s.spinner), i !== !1 && (i = e.extend({
                color: n || r.css("color")
            }, e.fn.spin.presets[i] || i), s.spinner = new t(i).spin(this))
        })
    }, e.fn.spin.presets = {
        tiny: {
            lines: 8,
            length: 2,
            width: 2,
            radius: 3
        },
        small: {
            lines: 8,
            length: 4,
            width: 3,
            radius: 5
        },
        large: {
            lines: 10,
            length: 8,
            width: 4,
            radius: 8
        }
    }
}), ! function(e) {
    e(function() {
        "use strict";
        var t = {
                lines: 8,
                length: 3,
                width: 2,
                radius: 8,
                color: "#000",
                corners: 1,
                speed: 1.6,
                trail: 44,
                shadow: !1,
                top: "auto",
                left: "auto"
            },
            i = {
                lines: 8,
                length: 3,
                width: 2,
                radius: 4
            },
            n = {
                lines: 7,
                length: 3,
                width: 2,
                radius: 3
            },
            r = {
                left: 0
            },
            s = {
                left: "right"
            },
            a = {
                color: "#000"
            },
            o = {
                lines: 17,
                length: 0,
                width: 5,
                radius: 6,
                corners: 1,
                rotate: 1,
                direction: 1,
                color: "#ccc",
                speed: 1.5,
                trail: 100,
                shadow: !1,
                hwaccel: !1,
                left: "right"
            },
            l = {
                lines: 15,
                length: 0,
                width: 8,
                radius: 30,
                corners: 1,
                rotate: 0,
                direction: 1,
                color: "#000",
                speed: 1,
                trail: 40,
                shadow: !1,
                hwaccel: !1
            };
        e(".sloading").livequery(function() {
            var c = e.extend({}, t);
            if (e(e(this).parent()).is(":hidden") && e(this).addClass("loading-align"), e(this).hasClass("loading-small") && e.extend(c, i), e(this).hasClass("loading-tiny") && e.extend(c, n), e(this).hasClass("loading-with-text")) {
                e(this).hasClass("loading-align") || e(this).addClass("loading-align");
                var d = -(e(this).find("span").width() / 4) - 10;
                e.extend(c, {
                    left: d
                })
            }
            e(this).hasClass("loading-left") ? e.extend(c, r) : e(this).hasClass("loading-right") ? e.extend(c, s) : e(this).hasClass("redactor-loading") && e.extend(c, a), e(this).hasClass("loading-circle") && e.extend(c, o), e(this).hasClass("loading-circle-large") && e.extend(c, l), e(this).spin(c)
        }, function() {
            e(this).spin(!1)
        })
    })
}(window.jQuery),
/* ========================================================
 * bootstrap-tabs.js v1.4.0
 * http://twitter.github.com/bootstrap/javascript.html#tabs
 * ========================================================
 * Copyright 2011 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ======================================================== */
! function(e) {
    "use strict";

    function t(e, t) {
        t.find("> .active").removeClass("active").find("> .dropdown-menu > .active").removeClass("active"), e.addClass("active"), e.parent(".dropdown-menu") && e.closest("li.dropdown").addClass("active")
    }

    function i(i) {
        var n, r, s = e(this),
            a = s.closest("ul:not(.dropdown-menu)"),
            o = s.attr("href");
        if (/^#\w+/.test(o)) {
            if (i.preventDefault(), window.location.hash = o, s.parent("li").hasClass("active")) return;
            s.data("remoteLoad") && (e(o).load(s.data("remoteLoad")), s.data("remoteLoad", !1)), n = a.find(".active a").last()[0], r = e(/([^\/]+$)/i.exec(o).last()), t(s.parent("li"), a), t(r, r.parent()), s.trigger({
                type: "change",
                relatedTarget: n
            })
        }
    }
    e.fn.tabs = e.fn.pills = function(t) {
        return this.each(function() {
            e(this).delegate(t || ".tabs li > a, .pills > li > a", "click", i)
        })
    }, e(document).ready(function() {
        e("body").tabs("ul[data-tabs] li > a, ul[data-pills] > li > a")
    })
}(window.jQuery || window.ender),
/* ===========================================================
 * bootstrap-popover.js v1.4.0
 * http://twitter.github.com/bootstrap/javascript.html#popover
 * ===========================================================
 * Copyright 2011 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * =========================================================== */
! function(e) {
    "use strict";
    var t = function(t, i) {
        this.$element = e(t), this.options = i, this.enabled = !0, this.fixTitle()
    };
    t.prototype = e.extend({}, e.fn.twipsy.Twipsy.prototype, {
        setContent: function() {
            if (this.options.reloadContent || !this.$element.attr("twipsy-content-set")) {
                var e = this.tip();
                e.find(".title")[this.options.html ? "html" : "text"](this.getTitle()), e.find(".content > *")[this.options.html ? "html" : "text"](this.getContent()), e[0].className = "popover", this.$element.attr("twipsy-content-set", !0)
            }
        },
        hasContent: function() {
            return this.getTitle() || this.getContent()
        },
        getContent: function() {
            var e, t = this.$element;
            return this.options, "string" == typeof this.options.content ? e = t.attr(this.options.content) : "function" == typeof this.options.content && (e = this.options.content.call(this.$element[0])), e
        },
        tip: function() {
            return this.$tip || (this.$tip = e('<div class="popover" />').html(this.options.template)), this.$tip
        }
    }), e.fn.popover = function(i) {
        return "object" == typeof i && (i = e.extend({}, e.fn.popover.defaults, i)), e.fn.twipsy.initWith.call(this, i, t, "popover"), this
    };
    var i = "rtl" == e("html").attr("dir") ? "left" : "right";
    e.fn.popover.defaults = e.extend({}, e.fn.twipsy.defaults, {
        placement: i,
        content: "data-content",
        reloadContent: !0,
        template: '<div class="arrow"></div><div class="inner"><h3 class="title"></h3><div class="content"><p></p></div></div>'
    }), e.fn.twipsy.rejectAttrOptions.push("content")
}(window.jQuery || window.ender), ! function(e) {
    "use strict";

    function t(e, t, i) {
        return "function" == typeof e ? e.apply(t, i) : e
    }
    var i = function(t, i) {
        this.$element = e(t), this.options = i, this.enabled = !0
    };
    i.prototype = e.extend({}, e.fn.twipsy.Twipsy.prototype, {
        show: function() {
            var i, n, r, s, a, o, l, c, d;
            if (this.hasContent() && this.enabled) {
                switch (a = this.tip(), l = e(a.find(".arrow")[0]), this.setContent(), this.options.animate && a.addClass("fade"), a.find(".fd-popover-close").on("click", function() {
                    a.hide()
                }), a.css({
                    top: 0,
                    left: 0,
                    display: "block"
                }).insertAfter(this.$element), d = this.$element.offset(), i = e.extend({}, this.$element.offset(), {
                    width: this.$element[0].offsetWidth,
                    height: this.$element[0].offsetHeight
                }), n = a[0].offsetWidth, r = a[0].offsetHeight, s = t(this.options.placement, this, [a[0], this.$element[0]])) {
                    case "below":
                        o = {
                            top: 0,
                            left: i.left + i.width / 2 - n / 2
                        };
                        break;
                    case "above":
                        o = {
                            top: 0,
                            left: i.left + i.width / 2 - n / 2
                        };
                        break;
                    case "left":
                        c = {
                            top: d.top / 2 - this.$element[0].offsetHeight
                        }, o = {
                            top: d.top,
                            left: i.left - n - this.options.offset
                        };
                        break;
                    case "topLeft":
                        o = {
                            top: 0,
                            left: i.left - n - this.options.offset
                        };
                        break;
                    case "right":
                        o = {
                            top: 0,
                            left: i.left + this.options.offset
                        };
                        break;
                    case "topRight":
                        o = {
                            top: 0,
                            left: i.left + this.options.offset
                        };
                        break;
                    case "belowLeft":
                        o = {
                            top: 0,
                            left: i.left + i.width - n + this.options.offset
                        };
                        break;
                    case "belowRight":
                        o = {
                            top: 0,
                            left: i.left + i.width / 2 - n / 5 + this.options.offset
                        }
                }
                a.css(o).addClass(s).addClass("in")
            }
        },
        setContent: function() {
            if (this.options.reloadContent || !this.$element.attr("twipsy-content-set")) {
                var e = this.tip();
                e.find(".content")[this.options.html ? "html" : "text"](this.getContent()), e[0].className = "fd-popover", this.$element.attr("twipsy-content-set", !0)
            }
        },
        hasContent: function() {
            return this.getContent()
        },
        getContent: function() {
            var e, t = this.$element;
            return this.options, "string" == typeof this.options.content ? e = t.attr(this.options.content) : "function" == typeof this.options.content && (e = this.options.content.call(this.$element[0])), e
        },
        tip: function() {
            return this.$tip || (this.$tip = e('<div class="fd-popover"/>').html(this.options.template)), this.$tip
        }
    }), e.fn.fdpopover = function(t) {
        return "object" == typeof t && (t = e.extend({}, e.fn.fdpopover.defaults, t)), e.fn.twipsy.initWith.call(this, t, i, "fdpopover"), this
    };
    var n = "rtl" == e("html").attr("dir") ? "left" : "right";
    e.fn.fdpopover.defaults = e.extend({}, e.fn.twipsy.defaults, {
        placement: n,
        content: "data-content",
        reloadContent: !0,
        template: '<div class="arrow"></div><div class="inner"><button type="button" class="fd-popover-close" data-dismiss=fd-popover">&times;</button><div class="content"></div></div>'
    }), e.fn.twipsy.rejectAttrOptions.push("content")
}(window.jQuery),
function(e) {
    e.fn.showAsDynamicMenu = function() {
        this.each(function(s, a) {
            e(a).bind("click", function(s) {
                s.preventDefault(), s.stopPropagation();
                var l;
                if (1 != e(a).data("options-fetched")) {
                    l = t();
                    var c = i(l);
                    c.data("parent", e(a)), c.insertAfter(e(a)), e(a).data("menuid", l), "undefined" != typeof e(a).data("options-url") && "" != e(a).data("options-fetched") ? n(a, l) : r(a, l)
                } else l = e(a).data("menuid");
                var d = e("#menu_" + l);
                d.show().css("visibility", "visible"), e(document).data({
                    "active-menu": !0,
                    "active-menu-element": d,
                    "active-menu-parent": a
                }), e(a).addClass("selected"), h(l), o(l), u(l)
            })
        });
        var t = function() {
                "undefined" == typeof e(document).data("dynamic-menu-count") && e(document).data("dynamic-menu-count", 0);
                var t = e(document).data("dynamic-menu-count") + 1;
                return e(document).data("dynamic-menu-count", t), t
            },
            i = function(t) {
                return e("<div>").attr("id", "menu_" + t).addClass("sloading loading-small  fd-ajaxmenu").html('<div class="contents"></div>')
            },
            n = function(t, i) {
                e.ajax({
                    url: e(t).data("options-url"),
                    success: function(n) {
                        var r = e(t).children(".result").first().text();
                        s(i, n, r), e(t).data("options-fetched", !0)
                    }
                })
            },
            r = function(t, i) {
                var n = e(t).children(".result").first().text();
                s(i, e(e(t).data("options")).html(), n), e(t).data("options-fetched", !0)
            },
            s = function(t, i, n) {
                var r = e("#menu_" + t).find(".contents").first();
                e("#menu_" + t).removeClass("sloading loading-small"), r.html(i), a(t, n), l(t), r.children().not(".seperator").length > 10 ? e("#menu_" + t).addClass("hasSearch") : e("#menu_" + t).removeClass("hasSearch"), c(t)
            },
            a = function(t, i) {
                var n = !1;
                e("#menu_" + t + " .contents").children().each(function() {
                    (!n && e(this).data("text") == i || e(this).text() == i) && (e(this).addClass("active").prepend('<span class="icon ticksymbol"></span>'), n = !0, u(t))
                })
            },
            o = function(t) {
                e("#menu_" + t + " .menu_search").val("").focus()
            },
            l = function(t) {
                var i = e('<div class="search_container"><input class="menu_search" type="text" placeholder="Search" /> </div>');
                e("#menu_" + t).prepend(i);
                var n = e('<div class="no_results hide"> No matches found </div>');
                e("#menu_" + t).append(n);
                var r = e("#menu_" + t + " .menu_search");
                r.bind("keydown, keypress", function(e) {
                    return 13 == e.keyCode ? !1 : void 0
                }), r.bind("keyup", function(i) {
                    switch (i.keyCode) {
                        case 40:
                        case 38:
                        case 13:
                            return i.preventDefault(), i.stopPropagation(), d(i.keyCode, t), !1;
                        default:
                            var n = r.val().trim();
                            regex = new RegExp(n, "i");
                            var s = e("#menu_" + t + " .contents");
                            if ("" != n) {
                                e("#menu_" + t + " .contents .selected").removeClass("selected"), e("#menu_" + t).data("currentactive", ""), s.find(".seperator").addClass("hide"), e("#menu_" + t + " .contents").children().not(".seperator").each(function() {
                                    -1 == e(this).text().search(regex) ? e(this).addClass("hide") : e(this).removeClass("hide")
                                });
                                var a = e("#menu_" + t + " .contents a").not(".hide");
                                0 == a.length ? e("#menu_" + t + " .no_results").removeClass("hide") : e("#menu_" + t + " .no_results").addClass("hide")
                            } else e("#menu_" + t + " .no_results").addClass("hide"), s.children().removeClass("hide"), s.find(".seperator").removeClass("hide"), p(t);
                            u(t)
                    }
                })
            },
            c = function(t) {
                e("body").on("mouseover", "#menu_" + t + " .contents a", function() {
                    p(t);
                    var i = e("#menu_" + t + " .contents a").not(".hide"),
                        n = e(this).addClass("selected"),
                        r = i.index(n);
                    e("#menu_" + t).data("currentactive", n), e("#menu_" + t).data("selection_position", r)
                })
            },
            d = function(t, i) {
                switch (t) {
                    case 40:
                        f(1, i);
                        break;
                    case 38:
                        f(-1, i);
                        break;
                    case 13:
                        var n = e("#menu_" + i).data("currentactive");
                        "" != e("#menu_" + i).data("currentactive") && n.trigger("click")
                }
            },
            u = function(t) {
                p(t);
                var i = e("#menu_" + t + " .contents a").not(".hide"),
                    n = e(i.get(0)).addClass("selected");
                e("#menu_" + t).data("currentactive", n), e("#menu_" + t).data("selection_position", 0)
            },
            h = function(t) {
                e("#menu_" + t + " .contents .hide").removeClass("hide")
            },
            p = function(t) {
                var i = e("#menu_" + t).data("currentactive");
                e(i).removeClass("selected"), e("#menu_" + t).data("selection_position", 0), e("#menu_" + t).data("currentactive", void 0)
            },
            f = function(t, i) {
                var n = e("#menu_" + i + " .contents a").not(".hide"),
                    r = e("#menu_" + i).data("currentactive"),
                    s = e("#menu_" + i).data("selection_position");
                s = "undefined" == typeof s ? -1 : s, e(r).removeClass("selected"), s = Math.min(n.length - 1, Math.max(0, s + t)), r = e(n.get(s)).addClass("selected"), t > 0 && s > 15 ? e("#menu_" + i + " .contents").get(0).scrollTop += 20 : e("#menu_" + i + " .contents").get(0).scrollTop -= 20, e("#menu_" + i).data("currentactive", r), e("#menu_" + i).data("selection_position", s)
            }
    }
}(jQuery), "undefined" == typeof Autocompleter && (Autocompleter = {}), Autocompleter.Json = Class.create(Autocompleter.Base, {
    initialize: function(e, t, i, n) {
        n = n || {}, this.baseInitialize(e, t, n), this.lookupFunction = i, this.options.choices = n.choices || 10
    },
    getUpdatedChoices: function() {
        this.lookupFunction(this.getToken().toLowerCase(), this.updateJsonChoices.bind(this))
    },
    updateJsonChoices: function(e) {
        this.updateChoices("<ul>" + e.slice(0, this.options.choices).map(this.jsonChoiceToListChoice.bind(this)).join("") + "</ul>")
    },
    jsonChoiceToListChoice: function(e) {
        return "<li>" + e.escapeHTML() + "</li>"
    }
}), Autocompleter.RateLimiting = function() {
    this.currentRequest = null, this.scheduledRequest = null
}, Autocompleter.RateLimiting.prototype = {
    schedule: function(e, t, i) {
        this.scheduledRequest = {
            f: e,
            searchTerm: t,
            callback: i
        }, this._sendRequest()
    },
    _sendRequest: function() {
        this.currentRequest || (this.currentRequest = this.scheduledRequest, this.scheduledRequest = null, this.currentRequest.f(this.currentRequest.searchTerm, this._callback.bind(this)))
    },
    _callback: function(e) {
        this.currentRequest.callback(e), this.currentRequest = null, this.scheduledRequest && this._sendRequest()
    }
}, Autocompleter.Cache = Class.create({
    initialize: function(e, t) {
        this.cache = new Hash, this.backendLookup = e, this.rateLimiter = new Autocompleter.RateLimiting, this.options = Object.extend({
            choices: 10,
            fuzzySearch: !1,
            searchKey: "searchKey"
        }, t || {})
    },
    lookup: function(e, t) {
        return this._lookupInCache(e, null, t) || this.rateLimiter.schedule(this.backendLookup, e, this._storeInCache.curry(e, t).bind(this))
    },
    _lookupInCache: function(e, t, i) {
        var t = t || e,
            n = this.cache.get(t);
        if (_results_array = n, null == n) return t.length > 1 ? void 0 == n || n.length ? !1 : this._lookupInCache(e, t.substr(0, t.length - 1), i) : !1;
        if (e != t) {
            if (n = this._localSearch(n, e), n.length < this.options.choices) return !1;
            this._storeInCache(e, null, n)
        }
        return i(n.slice(0, this.options.choices)), !0
    },
    _localSearch: function(e, t) {
        for (var i = this.options.fuzzySearch ? new RegExp(t.gsub(/./, ".*#{0}"), "i") : new RegExp(t, "i"), n = new Array, r = null, s = 0, a = e.length; a > s; ++s) r = e[s], i.test("object" == typeof r ? r[this.options.searchKey] : r) && n.push(r);
        return n
    },
    _storeInCache: function(e, t, i) {
        this.cache.set(e, i), t && t(i.slice(0, this.options.choices))
    }
}), Autocompleter.MultiValue = Class.create({
    options: $H({}),
    element: null,
    dataFetcher: null,
    createSelectedElement: function(e, t) {
        var i = new Element("a");
        i.className = "close-link", i.observe("click", function(e) {
            this.removeEntry(e.element().up("li")), e.stop()
        }.bind(this));
        var n = new Element("input", {
            type: "hidden",
            value: e
        });
        n.name = this.name + "[]";
        var r = new Element("li", {
            choice_id: e
        });
        return r.className = "choice", r.insert(("" + t).escapeHTML()).insert(i).insert(n)
    },
    initialize: function(e, t, i, n) {
        this.options = n || {};
        var r = $(e);
        this.name = r.name, this.choices_visible = !1, this.form = r.up("form"), this.dataFetcher = t, this.active = !1, this.options.ignoreQuotedComma = this.options.ignoreQuotedComma || !1, this.acceptNewValues = this.options.acceptNewValues || !1, this.options.frequency = this.options.frequency || .4, this.options.allowSpaces = this.options.allowSpaces || !1, this.options.minChars = this.options.minChars || 2, this.options.tabindex = this.options.tabindex || r.readAttribute("tabindex") || "", this.options.placeHolder = this.options.placeHolder || "", this.options.onShow = this.options.onShow || function(e, t) {
            if (!t.style.position || "absolute" == t.style.position) {
                t.style.position = "absolute";
                try {
                    t.clonePosition(e, {
                        setHeight: !1,
                        offsetTop: e.offsetHeight
                    })
                } catch (i) {}
            }
            Effect.Appear(t, {
                duration: .15
            })
        }, this.options.onHide = this.options.onHide || function(e, t) {
            new Effect.Fade(t, {
                duration: .15
            })
        }, this.searchField = new Element("input", {
            type: "text",
            autocomplete: "off",
            tabindex: this.options.tabindex,
            placeholder: this.options.placeHolder
        }), this.searchFieldItem = new Element("li").update(this.searchField), this.searchFieldItem.className = "search_field_item", this.holder = new Element("ul", {
            style: r.readAttribute("style")
        }).update(this.searchFieldItem), this.holder.className = "multi_value_field", r.insert({
            before: this.holder
        }), r.remove(), this.choicesHolderList = new Element("ul"), this.choicesHolder = new Element("div").update(this.choicesHolderList), this.choicesHolder.className = "autocomplete", this.choicesHolder.style.position = "absolute", this.holder.insert({
            after: this.choicesHolder
        }), this.choicesHolder.hide(), Event.observe(this.holder, "click", Form.Element.focus.curry(this.searchField)), Event.observe(this.searchField, "keydown", this.onSearchFieldKeyDown.bindAsEventListener(this)), this.acceptNewValues && (Event.observe(this.searchField, "keyup", this.onSearchFieldKeyUp.bindAsEventListener(this)), Event.observe(this.searchField, "blur", this.onSearchFieldBlur.bindAsEventListener(this))), Event.observe(this.searchField, "focus", this.getUpdatedChoices.bindAsEventListener(this)), Event.observe(this.searchField, "focus", this.show.bindAsEventListener(this)), Event.observe(this.searchField, "blur", this.hide.bindAsEventListener(this)), this.setEmptyValue(), (i || []).each(function(e) {
            this.addEntry(this.getValue(e), this.getTitle(e))
        }, this)
    },
    show: function() {
        this.choices_visible = !0, this.choicesHolderList.empty() || "none" == Element.getStyle(this.choicesHolder, "display") && this.options.onShow(this.holder, this.choicesHolder)
    },
    hide: function() {
        this.stopIndicator(), "none" != Element.getStyle(this.choicesHolder, "display") && (this.options.onHide(this.element, this.choicesHolder), this.choices_visible = !1), this.iefix && Element.hide(this.iefix)
    },
    onSearchFieldKeyDown: function(e) {
        if (this.active) switch (e.keyCode) {
            case Event.KEY_TAB:
            case Event.KEY_RETURN:
                this.selectEntry(), e.stop();
            case Event.KEY_ESC:
                return this.hide(), this.active = !1, e.stop(), void 0;
            case Event.KEY_LEFT:
            case Event.KEY_RIGHT:
                return;
            case Event.KEY_UP:
                return this.markPrevious(), this.render(), e.stop(), void 0;
            case Event.KEY_DOWN:
                return this.markNext(), this.render(), e.stop(), void 0
        } else {
            if (e.keyCode == Event.KEY_TAB || e.keyCode == Event.KEY_RETURN || Prototype.Browser.WebKit > 0 && 0 == e.keyCode) return;
            if (e.keyCode == Event.KEY_BACKSPACE && e.element().getValue().blank()) {
                var t = e.element().up("li.search_field_item").previous("li.choice");
                t && this.removeEntry(t)
            }
        }
        this.changed = !0, this.hasFocus = !0, this.observer && clearTimeout(this.observer), this.observer = setTimeout(this.onObserverEvent.bind(this), 1e3 * this.options.frequency)
    },
    onSearchFieldKeyUp: function(e) {
        var t = "";
        if (188 == e.keyCode || 32 == e.keyCode) {
            var i = $F(e.element()),
                n = 0;
            188 == e.keyCode ? void 0 != this.getEntry(0) ? this.selectEntry() : n = i.indexOf(",") : 32 != e.keyCode || this.options.allowSpaces || (n = i.indexOf(" ")), t = i.substr(0, n).toLowerCase().strip()
        }
        t.blank() || (this.addEntry(t, t), e.element().value = i.substring(n + 1, i.length))
    },
    onSearchFieldBlur: function(e) {
        this.addNewValueFromSearchField.bind(this).delay(0, e.element()), this.choices_visible && this.selectEntry()
    },
    addNewValueFromSearchField: function(e) {
        var t = $F(e).strip();
        t.blank() || (this.addEntry(t, t), e.value = "")
    },
    onObserverEvent: function() {
        this.changed = !1, this.tokenBounds = null, this.getToken().length >= this.options.minChars ? this.getUpdatedChoices() : (this.active = !1, this.hide())
    },
    getToken: function() {
        return this.searchField.value
    },
    markPrevious: function() {
        this.index > 0 ? this.index-- : this.index = this.entryCount - 1
    },
    markNext: function() {
        this.index < this.entryCount - 1 ? this.index++ : this.index = 0
    },
    getEntry: function(e) {
        return this.choicesHolderList.childNodes[e]
    },
    getCurrentEntry: function() {
        return this.getEntry(this.index)
    },
    selectEntry: function() {
        try {
            this.active = !1;
            var e = this.getCurrentEntry();
            this.addEntry(e.choiceId, e.textContent || e.innerText, !0), this.searchField.clear(), this.searchField.focus()
        } catch (t) {}
    },
    addEntry: function(e, t, i) {
        var n, r = [e],
            s = [t];
        for (!i && this.options.separatorRegEx && (this.options.ignoreQuotedComma ? (r = e.match(this.options.separatorRegEx), s = t.match(this.options.separatorRegEx)) : (r = e.split(this.options.separatorRegEx), s = t.split(this.options.separatorRegEx))), n = 0; n < r.length; n++) {
            e = r[n], t = s[n], t = t || e, this.selectedEntries().include("" + e) || (this.searchFieldItem.insert({
                before: this.createSelectedElement(e, t)
            }), jQuery(this.searchField).trigger("added.Autocompleter"));
            var a = this.emptyValueElement();
            a && a.remove()
        }
        jQuery(this.searchField).removeAttr("placeholder")
    },
    removeEntry: function(e) {
        e = Object.isElement(e) ? e : this.holder.down("li[choice_id=" + e + "]"), e && (e.remove(), jQuery(this.searchField).trigger("removed.Autocompleter"), 0 == this.selectedEntries().length && (this.setEmptyValue(), jQuery(this.searchField).attr("placeholder", this.options.placeHolder)))
    },
    clear: function() {
        this.holder.select("li.choice").each(function(e) {
            this.removeEntry(e)
        }, this)
    },
    setEmptyValue: function() {
        this.emptyValueElement() || this.form.insert(jQuery("<input />").attr({
            type: "hidden",
            name: this.name
        }).addClass("emptyValueField").get(0))
    },
    emptyValueElement: function() {
        return this.form.down("input.emptyValueField[name='" + this.name + "']")
    },
    selectedEntries: function() {
        return this.form.select("input[type=hidden][name='" + this.name + "[]']").map(function(e) {
            return e.value
        })
    },
    startIndicator: function() {},
    stopIndicator: function() {},
    getUpdatedChoices: function() {
        this.startIndicator();
        var e = this.getToken();
        e.length > 0 ? this.dataFetcher(e, this.updateChoices.curry(e).bind(this)) : this.choicesHolderList.update()
    },
    updateChoices: function(e, t) {
        if (!this.changed && this.hasFocus) {
            this.entryCount = t.length, this.choicesHolderList.innerHTML = "", t.each(function(t, i) {
                this.choicesHolderList.insert(this.createChoiceElement(this.getValue(t), this.getTitle(t), i, e))
            }.bind(this));
            for (var i = 0; i < this.entryCount; i++) {
                var n = this.getEntry(i);
                n.choiceIndex = i, this.addObservers(n)
            }
            this.stopIndicator(), this.index = 0, 1 == this.entryCount && this.options.autoSelect ? (this.selectEntry(), this.hide()) : this.render()
        }
    },
    addObservers: function(e) {
        Event.observe(e, "mouseover", this.onHover.bindAsEventListener(this)), Event.observe(e, "mousedown", this.onClick.bindAsEventListener(this)), Event.observe(e, "touchend", this.onClick.bindAsEventListener(this))
    },
    onHover: function(e) {
        var t = Event.findElement(e, "LI");
        this.index != t.autocompleteIndex && (this.index = t.autocompleteIndex, this.render()), Event.stop(e)
    },
    onClick: function(e) {
        var t = Event.findElement(e, "LI");
        this.index = t.autocompleteIndex, this.selectEntry(), this.hide()
    },
    createChoiceElement: function(e, t, i) {
        var n = new Element("li", {
            choice_id: e
        });
        return n.innerHTML = ("" + t).escapeHTML(), n.choiceId = e, n.autocompleteIndex = i, n
    },
    render: function() {
        if (this.entryCount > 0) {
            for (var e = 0; e < this.entryCount; e++) this.index == e ? Element.addClassName(this.getEntry(e), "selected") : Element.removeClassName(this.getEntry(e), "selected");
            this.hasFocus && (this.show(), this.active = !0)
        } else this.active = !1, this.hide()
    },
    getTitle: function(e) {
        return Object.isArray(e) ? e[0] : e
    },
    getValue: function(e) {
        return Object.isArray(e) ? e[1] : e
    }
}), Autocompleter.PanedSearch = Class.create({
    options: $H({}),
    element: null,
    dataFetcher: null,
    initialize: function(e, t, i, n, r, s) {
        this.options = s || {}, this.resultTemplate = i;
        var a = $(e) || $$(e)[0],
            o = $(n) || $$(n)[0];
        this.result = o, this.name = a.name, this.dataFetcher = t, this.active = !1, this.acceptNewValues = this.options.acceptNewValues || !1, this.options.frequency = this.options.frequency || .4, this.options.allowSpaces = this.options.allowSpaces || !1, this.options.minChars = this.options.minChars || 2, this.options.tabindex = this.options.tabindex || a.readAttribute("tabindex") || "", this.options.onShow = this.options.onShow || function(e, t) {
            if (!t.style.position || "absolute" == t.style.position) {
                t.style.position = "absolute";
                try {
                    t.clonePosition(e, {
                        setHeight: !1,
                        offsetTop: e.offsetHeight
                    })
                } catch (i) {}
            }
            Effect.Appear(t, {
                duration: .15
            })
        }, this.options.afterPaneShow = this.options.afterPaneShow || function() {}, this.options.onHide = this.options.onHide || function() {}, this.searchField = a, this.choicesHolderList = new Element("ul"), this.choicesHolder = new Element("div").update(this.choicesHolderList), this.choicesHolder.className = "autocompletepane", $(o).insert(this.choicesHolder), Event.observe(this.searchField, "click", Form.Element.focus.curry(this.searchField)), Event.observe(this.searchField, "keydown", this.onSearchFieldKeyDown.bindAsEventListener(this)), this.acceptNewValues && Event.observe(this.searchField, "keyup", this.onSearchFieldKeyUp.bindAsEventListener(this)), Event.observe(this.searchField, "focus", this.getUpdatedChoices.bindAsEventListener(this)), Event.observe(this.searchField, "focus", this.show.bindAsEventListener(this)), Event.observe(this.searchField, "blur", this.hide.bindAsEventListener(this)), (r || []).each(function(e) {
            this.addEntry(this.getValue(e), this.getTitle(e))
        }, this)
    },
    show: function() {
        this.choicesHolderList.empty() || (this.choicesHolder.addClassName("sloading loading-small"), "none" == Element.getStyle(this.choicesHolder, "display") && (this.choicesHolder.update(), this.options.onShow(this.holder, this.choicesHolder)), this.options.afterPaneShow(), this.choicesHolder.removeClassName("sloading loading-small"))
    },
    hide: function() {
        this.stopIndicator(), "none" != Element.getStyle(this.choicesHolder, "display") && this.options.onHide(this.element, this.choicesHolder), this.iefix && Element.hide(this.iefix)
    },
    onSearchFieldKeyDown: function(e) {
        if (this.active) switch (e.keyCode) {
            case Event.KEY_TAB:
            case Event.KEY_RETURN:
                this.getEntry(this.index).click(), e.stop();
            case Event.KEY_ESC:
                return this.hide(), this.active = !1, e.stop(), void 0;
            case Event.KEY_LEFT:
            case Event.KEY_RIGHT:
                return;
            case Event.KEY_UP:
                return this.markPrevious(), this.render(), e.stop(), void 0;
            case Event.KEY_DOWN:
                return this.markNext(), this.render(), e.stop(), void 0
        } else {
            if (e.keyCode == Event.KEY_TAB || e.keyCode == Event.KEY_RETURN || Prototype.Browser.WebKit > 0 && 0 == e.keyCode) return;
            e.keyCode == Event.KEY_BACKSPACE && e.element().getValue().blank()
        }
        this.changed = !0, this.hasFocus = !0, this.observer && clearTimeout(this.observer), this.observer = setTimeout(this.onObserverEvent.bind(this), 1e3 * this.options.frequency)
    },
    onSearchFieldKeyUp: function(e) {
        var t = "";
        if (188 == e.keyCode || 32 == e.keyCode) {
            var i = $F(e.element()),
                n = 0;
            188 == e.keyCode ? n = i.indexOf(",") : 32 != e.keyCode || this.options.allowSpaces || (n = i.indexOf(" ")), t = i.substr(0, n).toLowerCase().strip()
        }
        t.blank() || (this.addEntry(t, t), e.element().value = i.substring(n + 1, i.length))
    },
    onObserverEvent: function() {
        this.changed = !1, this.tokenBounds = null, this.getToken().length >= this.options.minChars ? this.getUpdatedChoices() : (this.active = !1, this.hide())
    },
    getToken: function() {
        return this.searchField.value
    },
    markPrevious: function() {
        this.index > 0 ? this.index-- : this.index = this.entryCount - 1, this.getEntry(this.index).offsetTop + this.getEntry(this.index).getHeight() < this.result.getHeight() && (this.result.scrollTop -= this.getEntry(this.index).getHeight()), this.index == this.entryCount - 1 && (this.result.scrollTop = this.getEntry(0).getHeight() * this.entryCount)
    },
    markNext: function() {
        this.index < this.entryCount - 1 ? this.index++ : this.index = 0, this.getEntry(this.index).offsetTop + this.getEntry(this.index).getHeight() > this.result.getHeight() - 10 && (this.result.scrollTop += this.getEntry(this.index).getHeight()), this.getEntry(this.index).offsetTop + this.getEntry(this.index).getHeight() == this.getEntry(0).getHeight() + this.getEntry(0).offsetTop && (this.result.scrollTop = 0)
    },
    getEntry: function(e) {
        return this.choicesHolderList.childNodes[e]
    },
    getCurrentEntry: function() {
        return this.getEntry(this.index)
    },
    removeEntry: function(e) {
        e = Object.isElement(e) ? e : this.holder.down("li[choice_id=" + e + "]"), e && (e.remove(), 0 == this.selectedEntries().length && this.setEmptyValue())
    },
    clear: function() {
        this.holder.select("li.choice").each(function(e) {
            this.removeEntry(e)
        }, this)
    },
    startIndicator: function() {},
    stopIndicator: function() {},
    getUpdatedChoices: function() {
        this.startIndicator();
        var e = this.getToken();
        e.length > 0 ? this.dataFetcher(e, this.updateChoices.curry(e).bind(this)) : this.choicesHolderList.update()
    },
    updateChoices: function(e, t) {
        if (!this.changed && this.hasFocus) {
            this.entryCount = t.length, this.choicesHolderList.innerHTML = "", t.each(function(t, i) {
                this.choicesHolderList.insert(this.createChoiceElement(t, i, e))
            }.bind(this));
            for (var i = 0; i < this.entryCount; i++) {
                var n = this.getEntry(i);
                n.choiceIndex = i, this.addObservers(n)
            }
            this.stopIndicator(), this.index = 0, 1 == this.entryCount && this.options.autoSelect ? (this.selectEntry(), this.hide()) : this.render()
        }
    },
    addObservers: function(e) {
        Event.observe(e, "mouseover", this.onHover.bindAsEventListener(this)), Event.observe(e, "click", this.onClick.bindAsEventListener(this))
    },
    onHover: function() {},
    onClick: function(e) {
        var t = Event.findElement(e, "LI");
        this.index = t.autocompleteIndex
    },
    createChoiceElement: function(e, t) {
        var i = this.resultTemplate.evaluate(e);
        return i.choiceId = e.id || t, i.autocompleteIndex = t, i
    },
    render: function() {
        if (this.entryCount > 0) {
            for (var e = 0; e < this.entryCount; e++) this.index == e ? Element.addClassName(this.getEntry(e), "selected") : Element.removeClassName(this.getEntry(e), "selected");
            this.hasFocus && (this.show(), this.active = !0)
        } else this.active = !1, this.hide(), this.choicesHolderList.update('<div class="list-noinfo">No Matching Results</div>')
    }
}), window.fdUtil = {
    make_defined: function() {
        $A(arguments).each(function(e) {
            e = e || ""
        })
    }
}, window.FactoryUI = {
    link: function(e, t, i) {
        return jQuery("<a />").attr("href", t).addClass(i).text(e)
    },
    label: function(e, t) {
        return jQuery("<label />").addClass(t).text(e)
    },
    text: function(e, t, i, n) {
        var r = n || "text",
            s = e || "",
            a = t || "",
            o = i || "";
        return jQuery("<input type='text' />").prop({
            name: a,
            placeholder: s
        }).addClass(r).val(o)
    },
    multiple_text: function(e, t, i, n) {
        var r = n || "",
            s = e || "",
            a = t || "",
            o = i || "",
            l = jQuery("<input type='text' />").prop({
                name: a,
                placeholder: s
            }).addClass(r).val(o);
        return l
    },
    multiple_text_with_id: function(e, t, i, n, r) {
        if (e) {
            var s = r || "",
                a = t || "",
                o = i || "",
                l = n || "",
                c = jQuery("<input type='text' />").prop({
                    name: o,
                    placeholder: a
                }).addClass(s).val(l).data("initObject", e);
            return c
        }
    },
    date: function(e, t, i, n, r) {
        var s = n || "datepicker_popover",
            a = e || "",
            o = t || "",
            l = i || "",
            c = r || "mm-dd-YY";
        return jQuery("<div class='date-wrapper input-date-field'/>").append(jQuery("<input type='text' />").prop({
            name: o,
            placeholder: a,
            readonly: !0
        }).addClass(s).val(l).data("showImage", "true").data("dateFormat", c))
    },
    password: function(e, t, i, n) {
        var r = n || "text password",
            s = e || "",
            a = t || "",
            o = i || "";
        return jQuery("<input type='password' />").prop({
            name: a,
            placeholder: s
        }).addClass(r).val(o)
    },
    hidden: function(e, t) {
        var i = e || "",
            n = t || "";
        return jQuery("<input type='hidden' />").prop({
            name: i,
            value: n
        })
    },
    dropdown: function(e, t, i, n) {
        if (e) {
            var r = i || "dropdown",
                s = t || "",
                a = jQuery("<select />").prop({
                    name: s
                }).addClass(r);
            return n && a.data(n), e.each(function(e) {
                jQuery("<option />").text(e.value).appendTo(a).get(0).value = e.name
            }), jQuery(a)
        }
    },
    optgroup: function(e, t, i, n) {
        if (e) {
            var r = i || "dropdown",
                s = t || "",
                a = jQuery("<select />").prop({
                    name: s
                }).addClass(r);
            return n && a.data(n), e.each(function(e) {
                if (e.length > 0 && e[1] instanceof Array) {
                    var t = jQuery("<optgroup label='" + e[0] + "' />");
                    e[1].each(function(e) {
                        jQuery("<option />").text(e[1]).data("unique_action", e[2] || !1).appendTo(t).get(0).value = e[0]
                    }), t.appendTo(a)
                } else jQuery("<option />").text(e[1]).appendTo(a).get(0).value = e[0]
            }), jQuery(a)
        }
    },
    paragraph: function(e, t, i, n, r) {
        var s = n || "paragraph",
            a = t || "",
            o = i || "",
            l = r || "";
        return jQuery("<textarea />").prop({
            name: a,
            id: l
        }).addClass(s).val(o)
    },
    checkbox: function(e, t, i, n, r, s, a) {
        var o = n || "checkbox",
            l = e || "",
            c = t || "",
            d = "true" == i ? "checked" : "";
        return checkboxClass = s || "", divClass = a || "", labelBox = jQuery("<label />").addClass(o + divClass), checkBox = jQuery("<input type='checkbox' />").prop({
            name: c,
            checked: d,
            value: r || !0
        }).addClass(checkboxClass), labelBox.append(checkBox).append(l)
    },
    radiobutton: function(e, t, i, n, r) {
        var s = r || "radiolabel",
            a = e,
            o = t || "",
            l = i || "",
            c = n || e[0].name,
            d = jQuery("<div />"),
            u = 0;
        return a.each(function(e) {
            choice = jQuery("<input type='radio' />").prop({
                name: l,
                value: e.name,
                id: e.name + e.value + u
            }), c == e.name && choice.prop({
                checked: !0
            }), o = jQuery("<label  />").prop({
                "for": e.name + e.value + u++,
                "class": s
            }).text(e.value), d.append(choice).append(o)
        }), d
    }
};
var JSON;
JSON || (JSON = {}),
    function() {
        "use strict";

        function f(e) {
            return 10 > e ? "0" + e : e
        }

        function quote(e) {
            return escapable.lastIndex = 0, escapable.test(e) ? '"' + e.replace(escapable, function(e) {
                var t = meta[e];
                return "string" == typeof t ? t : "\\u" + ("0000" + e.charCodeAt(0).toString(16)).slice(-4)
            }) + '"' : '"' + e + '"'
        }

        function str(e, t) {
            var i, n, r, s, a, o = gap,
                l = t[e];
            switch (l && "object" == typeof l && "function" == typeof l.toJSON && (l = l.toJSON(e)), "function" == typeof rep && (l = rep.call(t, e, l)), typeof l) {
                case "string":
                    return quote(l);
                case "number":
                    return isFinite(l) ? String(l) : "null";
                case "boolean":
                case "null":
                    return String(l);
                case "object":
                    if (!l) return "null";
                    if (gap += indent, a = [], "[object Array]" === Object.prototype.toString.apply(l)) {
                        for (s = l.length, i = 0; s > i; i += 1) a[i] = str(i, l) || "null";
                        return r = 0 === a.length ? "[]" : gap ? "[\n" + gap + a.join(",\n" + gap) + "\n" + o + "]" : "[" + a.join(",") + "]", gap = o, r
                    }
                    if (rep && "object" == typeof rep)
                        for (s = rep.length, i = 0; s > i; i += 1) "string" == typeof rep[i] && (n = rep[i], r = str(n, l), r && a.push(quote(n) + (gap ? ": " : ":") + r));
                    else
                        for (n in l) Object.prototype.hasOwnProperty.call(l, n) && (r = str(n, l), r && a.push(quote(n) + (gap ? ": " : ":") + r));
                    return r = 0 === a.length ? "{}" : gap ? "{\n" + gap + a.join(",\n" + gap) + "\n" + o + "}" : "{" + a.join(",") + "}", gap = o, r
            }
        }
        "function" != typeof Date.prototype.toJSON && (Date.prototype.toJSON = function() {
            return isFinite(this.valueOf()) ? this.getUTCFullYear() + "-" + f(this.getUTCMonth() + 1) + "-" + f(this.getUTCDate()) + "T" + f(this.getUTCHours()) + ":" + f(this.getUTCMinutes()) + ":" + f(this.getUTCSeconds()) + "Z" : null
        }, String.prototype.toJSON = Number.prototype.toJSON = Boolean.prototype.toJSON = function() {
            return this.valueOf()
        });
        var cx = /[\u0000\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,
            escapable = /[\\\"\x00-\x1f\x7f-\x9f\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,
            gap, indent, meta = {
                "\b": "\\b",
                "	": "\\t",
                "\n": "\\n",
                "\f": "\\f",
                "\r": "\\r",
                '"': '\\"',
                "\\": "\\\\"
            },
            rep;
        "function" != typeof JSON.stringify && (JSON.stringify = function(e, t, i) {
            var n;
            if (gap = "", indent = "", "number" == typeof i)
                for (n = 0; i > n; n += 1) indent += " ";
            else "string" == typeof i && (indent = i);
            if (rep = t, t && "function" != typeof t && ("object" != typeof t || "number" != typeof t.length)) throw new Error("JSON.stringify");
            return str("", {
                "": e
            })
        }), "function" != typeof JSON.parse && (JSON.parse = function(text, reviver) {
            function walk(e, t) {
                var i, n, r = e[t];
                if (r && "object" == typeof r)
                    for (i in r) Object.prototype.hasOwnProperty.call(r, i) && (n = walk(r, i), void 0 !== n ? r[i] = n : delete r[i]);
                return reviver.call(e, t, r)
            }
            var j;
            if (text = String(text), cx.lastIndex = 0, cx.test(text) && (text = text.replace(cx, function(e) {
                    return "\\u" + ("0000" + e.charCodeAt(0).toString(16)).slice(-4)
                })), /^[\],:{}\s]*$/.test(text.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g, "@").replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, "]").replace(/(?:^|:|,)(?:\s*\[)+/g, ""))) return j = eval("(" + text + ")"), "function" == typeof reviver ? walk({
                "": j
            }, "") : j;
            throw new SyntaxError("JSON.parse")
        })
    }(),
    function(e) {
        function t(e, t) {
            var i = jQuery(t).data("requesterCheck"),
                n = jQuery(t).data("partialRequesterList") || [];
            return _user = jQuery(t).data("currentUser"), _requester = jQuery(t).data("initialRequester"), _requesterId = jQuery(t).data("initialRequesterid"), /(\b[-a-zA-Z0-9.'-_~!$&()*+;=:%+]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,15}\b)/.test(e) && (i = !0, jQuery("#helpdesk_ticket_requester_id").val("")), e == _user && (i = !0), e == _requester && jQuery("#helpdesk_ticket_requester_id") && jQuery("#helpdesk_ticket_requester_id").val() == _requesterId && (i = !0), n.each(function(t) {
                trim(e) == trim(t.details) && (i = !0)
            }), i
        }
        e.validator.addMethod("facebook", e.validator.methods.maxlength, "Your Facebook reply was over 8000 characters. You'll have to be more clever."), e.validator.addClassRules("facebook", {
            facebook: 8e3
        }), e.validator.addMethod("facebook-realtime", function(t, i) {
            return e(i).data("reply-count") >= 0 ? !0 : void 0
        }, "Oops! You have exceeded Facebook Messenger Platform's character limit. Please modify your response."), e.validator.addMethod("notEqual", function(t, i, n) {
            return (this.optional(i) || t).strip().toLowerCase() != e(n).val().strip().toLowerCase()
        }, "This element should not be equal to"), e.validator.addMethod("multiemail", function(t, i) {
            if (this.optional(i)) return !0;
            var n = t.split(new RegExp("\\s*,\\s*", "gi"));
            return valid = !0, e.each(n, function(e, t) {
                return valid = /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i.test(t), valid ? void 0 : !1
            }), valid
        }, "One or more email addresses are invalid."), e.validator.addClassRules("multiemail", {
            multiemail: !0
        }), e.validator.addMethod("tweet", function(t, i) {
            return e(i).data("tweet-count") >= 0 ? !0 : void 0
        }, "Oops! You have exceeded Twitter's character limit. You'll have to modify your response."), e.validator.addMethod("password_confirmation", function(t, i) {
            return e(i).val() == e("#password").val()
        }, "The passwords don't match. Please try again."), e.validator.addClassRules("password_confirmation", {
            password_confirmation: !0
        }), e.validator.addMethod("hours", function(e, t) {
            return hours = normalizeHours(e), t.value = hours, /^([0-9]*):([0-5][0-9])(:[0-5][0-9])?$/.test(hours)
        }, "Please enter a valid hours."), e.validator.addClassRules("hours", {
            hours: !0
        }), e.validator.addMethod("only_digits", function(e, t) {
            return /[0-9]+/.test(e) ? (jQuery("#text_" + t.id.match(/[0-9].+/)).removeClass("sla-error"), !0) : (jQuery("#text_" + t.id.match(/[0-9].+/)).addClass("sla-error"), !1)
        }, ""), e.validator.addMethod("ecommerce", function(t, i) {
            return e(i).data("ecommerce-count") >= 0 ? !0 : void 0
        }, "Your reply was over 2000 characters. You'll have to be more clever."), e.validator.addMethod("sla_min_time", function(e, t) {
            return e >= 900 ? (jQuery("#text_" + t.id.match(/[0-9].+/)).removeClass("sla-error"), !0) : (jQuery("#text_" + t.id.match(/[0-9].+/)).addClass("sla-error"), !1)
        }, ""), e.validator.addMethod("sla_max_time", function(e, t) {
            return 31536e3 >= e ? (jQuery("#text_" + t.id.match(/[0-9].+/)).removeClass("sla-error"), !0) : (jQuery("#text_" + t.id.match(/[0-9].+/)).addClass("sla-error"), !1)
        }, ""), e.validator.addClassRules("sla_time", {
            only_digits: !0,
            sla_min_time: !0,
            sla_max_time: !0
        }), e.validator.addMethod("domain_validator", function(e, t) {
            return this.optional(t) ? !0 : 0 == e.length ? !0 : (valid = /((http|https|ftp):\/\/)\w+/.test(e) ? !1 : /\w+[\-]\w+/.test(e) ? !0 : /\W\w*/.test(e) ? !1 : !0, /_+\w*/.test(e) && (valid = !1), valid)
        }, "Invalid URL format"), e.validator.addClassRules("domain_validator", {
            domain_validator: !0
        }), e.validator.addClassRules("url_validator", {
            url: !0
        }), e.validator.addMethod("url_without_protocol", function(t, i) {
            var n = i && e(i).data("domain") || baseurl,
                r = new RegExp("^(?!.*\\." + n + "$)[/\\w\\.-]+$");
            return t = trim(t), this.optional(i) || /^((https?|ftp):\/\/)?(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(t) && r.test(t)
        }, "Please enter a valid URL"), e.validator.addClassRules({
            url_without_protocol: {
                url_without_protocol: !0
            }
        }), e.validator.addMethod("requester", function(e, i) {
            return t(e, i)
        }, jQuery.validator.format('Please enter a valid requester details or <a href="#" id="add_requester_btn_proxy">add new requester.</a>')), e.validator.addClassRules("requester", {
            requester: !0
        }), e.validator.addMethod("agent_requester", function(e, i) {
            var n = t(e, i);
            return 0 === _results_array.length && (n = !1), n
        }, jQuery.validator.format("Please enter valid agent details")), e.validator.addClassRules("agent_requester", {
            agent_requester: !0
        }), e.validator.addMethod("require_from_group", function(t, i, n) {
            var r = n[0],
                s = n[1],
                a = e(s, i.form),
                o = a.filter(function() {
                    return "" != e(this).val()
                }),
                l = a.not(o);
            return o.length < r && l[0] == i ? !1 : !0
        }, jQuery.validator.format("Please enter a Email or Phone Number")), e.validator.addClassRules("require_from_group", {
            require_from_group: [1, ".user_info"]
        }), e.validator.addMethod("upload_size_validity", function(e, t) {
            if (window.FileReader) {
                var i = jQuery(t)[0].files;
                if (i.length) return filesize = i[0].size / 1048576, 15 > filesize
            }
            return !0
        }, jQuery.validator.format("Upload exceeds the available 15MB limit")), e.validator.addClassRules("upload_size_validity", {
            upload_size_validity: !0
        }), e.validator.addMethod("validate_image", function(e, t) {
            if (window.FileReader) {
                var i = jQuery(t)[0].files;
                if (i.length) {
                    var n = i[0];
                    return /^image*/.test(n.type)
                }
            }
            return !0
        }, jQuery.validator.format("Invalid image format")), e.validator.addClassRules("validate_image", {
            validate_image: !0
        }), e.validator.addMethod("at_least_one_item", function(t, i) {
            return 0 != e(e(i).data("selector")).length
        }, jQuery.validator.format("At least one role is required for the agent")), e.validator.addClassRules("at_least_one_item", {
            at_least_one_item: !0
        }), e.validator.addMethod("hhmm_time_duration", function(e) {
            return /(^[0-9]*$)|(^[0-9]*:([0-5][0-9]{0,1}|[0-9])$)|(^[0-9]*\.{1}[0-9]+$)/.test(e)
        }, "Please enter a valid time"), e.validator.addClassRules("hhmm_time_duration", {
            hhmm_time_duration: !0
        }), e.validator.addMethod("email", function(e, t) {
            return this.optional(t) || /^[a-zA-Z0-9.'-_~!$&()*+;=:%+]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/.test(e)
        }, "Please enter a valid email address."), e.validator.addMethod("useremail", function(t, i) {
            var n = this.optional(i) || /^[a-zA-Z0-9.'-_~!$&()*+;=:%+]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/.test(t);
            return n ? e(i).removeClass("email-error") : e(i).addClass("email-error"), n
        }, "Please enter a valid email address."), e.validator.addMethod("time_12", function(e) {
            if (!/^[0-9]{1,2}:[0-9]{1,2} [ap]m$/i.test(e)) return !1;
            var t = e.split(" "),
                i = t[0].split(":"),
                n = t[1],
                r = i[0],
                s = i[1];
            return "am" != n && "pm" != n ? !1 : 1 > r || r > 12 ? !1 : 0 > s || s > 59 ? !1 : !0
        }, "Invalid time."), e.validator.addClassRules("time-12", {
            time_12: !0
        }), e.validator.addMethod("remotevalidate", function(t, i, n) {
            if (this.optional(i)) return "dependency-mismatch";
            var r = e(i),
                s = this.previousValue(i);
            this.settings.messages[i.name] || (this.settings.messages[i.name] = {}), s.originalMessage = this.settings.messages[i.name].remotevalidate, this.settings.messages[i.name].remotevalidate = s.message, n = "string" == typeof n && {
                url: n
            } || n;
            var a = r.data("validateUrl") || n,
                o = r.data("validateName") || i.name;
            if (this.pending[i.name]) return "pending";
            if (s.old === t) return s.valid;
            s.old = t;
            var l = this;
            this.startRequest(i);
            var c = {};
            return c[o] = t, e.ajax(e.extend(!0, {
                url: a,
                mode: "abort",
                port: "validate" + i.name,
                dataType: "json",
                data: c,
                success: function(e) {
                    l.settings.messages[i.name].remotevalidate = s.originalMessage;
                    var t = e.success === !0;
                    if (t) {
                        var n = l.formSubmitted;
                        l.prepareElement(i), l.formSubmitted = n, l.successList.push(i), l.showErrors()
                    } else {
                        var r = {},
                            a = e.message || l.defaultMessage(i, "remote");
                        r[i.name] = s.message = a, l.showErrors(r)
                    }
                    s.valid = t, l.stopRequest(i, t)
                }
            }, n)), "pending"
        }, "Remote validation failed"), e.validator.addClassRules("remote-data", {
            remotevalidate: !0
        }), e.validator.addMethod("trim_spaces", function(e, t) {
            return t.value = trim(t.value), !0
        }, "Auto trim of leading & trailing whitespace"), e.validator.addClassRules("trim_spaces", {
            trim_spaces: !0
        }), e.validator.addMethod("required_redactor", function(t, i) {
            if (jQuery.browser.desktop) {
                var n = !1;
                return e(i).data("redactor") ? n = e(i).data("redactor").isNotEmpty() : e(i).data("froala.editor") && (n = !jQuery(".required_redactor").data("froala.editor").core.isEmpty()), n
            }
            var r = !0;
            return (null == t || "" == t) && (r = !1), r
        }, e.validator.messages.required), e.validator.addClassRules("required_redactor", {
            required_redactor: !0
        }), e.validator.addMethod("color_field", function(e) {
            return /^#(?:[0-9a-fA-F]{3}){1,2}$/.test(e)
        }, "Please enter a valid hex color value."), e.validator.addClassRules("color_field", {
            color_field: !0
        }), e.validator.addMethod("presence_in_list", function(t, i) {
            return -1 == e(i).data("list").indexOf(t.toLowerCase())
        }, "The name already exists."), e.validator.addClassRules("presence_in_list", {
            presence_in_list: !0
        }), e.validator.addMethod("zendesk_size_validity", function(e, t) {
            var i = !0;
            if (window.FileReader) {
                var n = jQuery(t)[0].files;
                n.length && (i = n[0].size / 1048576 <= 50)
            }
            return jQuery("#file_size_limit").toggle(!i), i
        }, jQuery.validator.format("")), e.validator.addClassRules("zendesk_size_validity", {
            zendesk_size_validity: !0
        }), e.validator.addMethod("regex_validity", function(t, i) {
            var n, r = e(i).data("regex-pattern"),
                s = r.match(new RegExp("^/(.*?)/([gimy]*)$"));
            return s && (n = new RegExp(s[1], s[2])), this.optional(i) || n.test(t)
        }, "Invalid value"), e.validator.addClassRules("regex_validity", {
            regex_validity: !0
        }), e.validator.addMethod("validate_regexp", function(e) {
            var t = new RegExp("^/(.*?)/([gimy]*)$"),
                i = e.match(t),
                n = !0;
            try {
                new RegExp(i[1], i[2])
            } catch (r) {
                n = !1
            }
            return n
        }, "Invalid Regular Expression"), e.validator.addMethod("ca_same_folder_validity", function(t, i) {
            var n = !0,
                r = e(i).data("currentFolder");
            return r == t && (n = !1), n
        }, "Cannot move to the same folder."), e.validator.addClassRules("ca_same_folder_validity", {
            ca_same_folder_validity: !0
        }), e.validator.addMethod("field_maxlength", e.validator.methods.maxlength, "Please enter less than 255 characters"), e.validator.addClassRules("field_maxlength", {
            field_maxlength: 255
        }), e.validator.addMethod("two_decimal", function(e) {
            return /^\d*(\.\d{0,2})?$/i.test(e)
        }, "Value cannot have more than 2 decimal digits"), e.validator.addClassRules("decimal", {
            number: !0,
            two_decimal: !0
        }), e.validator.addMethod("require_from_group", function(t, i, n) {
            var r = e(n[1], i.form),
                s = r.eq(0),
                a = s.data("valid_req_grp") ? s.data("valid_req_grp") : e.extend({}, this),
                o = r.filter(function() {
                    return a.elementValue(this)
                }).length >= n[0];
            return s.data("valid_req_grp", a), e(i).data("being_validated") || (r.data("being_validated", !0), r.each(function() {
                a.element(this)
            }), r.data("being_validated", !1)), o
        }, e.validator.format("Please fill at least {0} of these fields.")), e.validator.addClassRules("fillone", {
            require_from_group: [1, ".fillone"]
        }), e.validator.addMethod("portal_visibility_required", function(e) {
            return void 0 != e
        }, "Select atleast one portal."), e.validator.addClassRules("portal_visibility_required", {
            portal_visibility_required: !0
        }), e.validator.addMethod("valid_custom_headers", function(e) {
            return e.split("\n").filter(Boolean).every(function(e) {
                return e.includes(":")
            })
        }, e.validator.format("Please type custom header in the format -  header : value")), e.validator.addClassRules("valid_custom_headers", {
            valid_custom_headers: !0
        })
    }(jQuery), window.Helpdesk = window.Helpdesk || {},
    function() {
        Helpdesk.settings = {}, Helpdesk.calenderSettings = {
            insideCalendar: !1,
            closeCalendar: !1
        }
    }(window.jQuery);
var $J = jQuery.noConflict();
is_touch_device = function() {
        return !!("ontouchstart" in window) || !!("onmsgesturechange" in window)
    }, window.xhrPool = [],
    function(e) {
        e.oldajax = e.ajax, e.xhrPool_Abort = function() {
            if (window.xhrPool) {
                for (var e = 0; e < window.xhrPool.length; e++) window.xhrPool[e].abort();
                window.xhrPool = []
            }
        }, e.ajax = function(t) {
            if (t.persist) return e.oldajax(t);
            var i = t.complete || function() {};
            t.complete = function(e, t) {
                var n = window.xhrPool.indexOf(e);
                n > -1 && window.xhrPool.splice(n, 1), i(e, t)
            };
            var n = e.oldajax(t);
            return n && window.xhrPool.push(n), n
        }, e(document).ready(function() {
            function t() {
                e(".nav-drop .menu-box").hide(), e(".nav-drop .menu-trigger").removeClass("selected")
            }
            var i, n = null,
                r = !1;
            is_touch_device() && e("html").addClass("touch"), e.browser.msie && 10 == parseInt(e.browser.version) && e("html").addClass("ie ie10"), e("body").click(function(e) {
                hideWidgetPopup(e)
            }), hideWidgetPopup = function(t) {
                null == n || e(t.target).parents().hasClass("popover") || Helpdesk.calenderSettings.insideCalendar || (n.popover("hide"), n = null), Helpdesk.calenderSettings.closeCalendar && (Helpdesk.calenderSettings.insideCalendar = !1, Helpdesk.calenderSettings.closeCalendar = !1)
            }, hidePopover = function(t) {
                e.contains(this, t.relatedTarget) || (r && !e(t.relatedTarget).is("[rel=contact-hover]") && (i = setTimeout(function() {
                    n && (n.popover("hide"), r = !1)
                }, 1e3)), e(t.relatedTarget).is("[rel=more-agents-hover]") || (i = setTimeout(function() {
                    e(".hover-card-agent").parent().remove()
                }, 500)))
            }, hideActivePopovers = function(t) {
                e("[rel=widget-popover],[rel=contact-hover],[rel=hover-popover],[rel=more-agents-hover],[rel=ff-alert-popover]").each(function() {
                    t.target != e(this).get(0) && e(this).popover("hide")
                })
            }, e("body").on("mouseleave", "div.popover", hidePopover).on("mouseenter", "div.popover", function() {
                clearTimeout(i)
            }), e("a[rel=popover], a[rel=widget-popover]").popover({
                delayOut: 300,
                trigger: "manual",
                offset: 5,
                html: !0,
                reloadContent: !1,
                template: '<div class="arrow"></div><div class="inner"><div class="content"><div></div></div></div>',
                content: function() {
                    return e("#" + e(this).attr("data-widget-container")).html()
                }
            }), e("body").on("mouseenter", "a[rel=more-agents-hover]", function(t) {
                t.preventDefault();
                var s = e(this),
                    a = setTimeout(function() {
                        clearTimeout(i), hideActivePopovers(t), n = s.popover("show"), r = !0
                    }, 300);
                s.data("timeoutDelayShow", a)
            }).on("mouseleave", function() {
                clearTimeout(e(this).data("timeoutDelayShow")), i = setTimeout(function() {
                    e(".hover-card-agent").parent().remove()
                }, 1e3)
            }), e("body").on("mouseenter", "a[rel=contact-hover],[rel=hover-popover]", function(t) {
                t.preventDefault();
                var s = e(this),
                    a = setTimeout(function() {
                        clearTimeout(i), hideActivePopovers(t), n = s.popover("show"), r = !0
                    }, 500);
                s.data("timeoutDelayShow", a)
            }).on("mouseleave", "a[rel=contact-hover],[rel=hover-popover]", function() {
                clearTimeout(e(this).data("timeoutDelayShow")), i = setTimeout(function() {
                    n && n.popover("hide"), r = !1
                }, 1e3)
            }), e("body").on("mouseenter", "[rel=ff-alert-popover]", function(t) {
                t.preventDefault();
                var s = e(this),
                    a = setTimeout(function() {
                        clearTimeout(i), hideActivePopovers(t), n = s.popover("show"), r = !0
                    }, 500);
                s.data("timeoutDelayShow", a)
            }).on("mouseleave", "[rel=ff-alert-popover]", function() {
                clearTimeout(e(this).data("timeoutDelayShow")), i = setTimeout(function() {
                    e(".hover-card-agent").parent().remove()
                }, 300)
            }), e("body").on("mouseenter", "a[rel=ff-hover-popover]", function(t) {
                if (t.preventDefault(), !freshfoneuser.isOffline()) {
                    var s = e(this),
                        a = setTimeout(function() {
                            clearTimeout(i), hideActivePopovers(t), n = s.popover("show"), r = !0
                        }, 300);
                    s.data("timeoutDelayShow", a)
                }
            }).on("mouseleave", "a[rel=ff-hover-popover]", function() {
                clearTimeout(e(this).data("timeoutDelayShow")), i = setTimeout(function() {
                    e(".hover-card-agent").parent().remove()
                }, 1e3)
            }), e("body").on("click", "a[rel=widget-popover], a[rel=click-popover-below-left]", function(t) {
                t.preventDefault(), t.stopPropagation(), clearTimeout(i), r = !1, hideActivePopovers(t), n = e(this).popover("show")
            }), e("body").on("input propertychange", "textarea[maxlength]", function() {
                var t = e(this).attr("maxlength");
                e(this).val().length > t && e(this).val(e(this).val().substring(0, t))
            }), e("body").on("afterShow", "[rel=remote]", function() {
                var t = e(this);
                t.data("loaded") || (t.append("<div class='sloading loading-small loading-block'></div>"), e.ajax({
                    type: "GET",
                    url: t.data("remoteUrl"),
                    dataType: "html",
                    error: function(e) {
                        console.log("failed", e)
                    },
                    success: function(e) {
                        t.html(e), t.data("loaded", !0), t.trigger("remoteLoaded")
                    }
                }))
            }), e("body").on("reload", "[rel=remote]", function() {
                var t = e(this);
                t.empty(), t.data("loaded", !1), t.trigger("afterShow")
            }), e("input[type=checkbox].iphone").each(function() {
                var t = e(this),
                    i = t.attr("data-active-text") || "Yes",
                    n = t.attr("data-inactive-text") || "No";
                t.wrap('<div class="stylised iphone" />'), t = t.parent(), t.append('<span class="text">' + i + '</span><span class="other"></span>'), t.children("input[type=checkbox]").addClass("hide"), t.bind("click", function(t) {
                    t.preventDefault(), t.stopPropagation(), e(this).toggleClass("inactive"), e(this).children(".text").text(e(this).hasClass("inactive") ? n : i), e(this).hasClass("inactive") ? e(this).children("input[type=checkbox]").removeAttr("checked") : e(this).children("input[type=checkbox]").prop("checked", !0)
                })
            }), e.validator.setDefaults({
                errorPlacement: function(e, t) {
                    "checkbox" == t.prop("type") || t.hasClass("portal-logo") || t.hasClass("portal-fav-icon") ? e.insertAfter(t.parent()) : e.insertAfter(t)
                },
                onkeyup: !1,
                focusCleanup: !1,
                focusInvalid: !0,
                ignore: "select.nested_field:empty, .portal_url:not(:visible), .ignore_on_hidden:not(:visible)"
            }), e("body").on("change", "#helpdesk_ticket_status", function() {
                var t = e(".required_closure");
                if (0 != t.length) {
                    var i = e("#helpdesk_ticket_status option:selected").val();
                    "5" === i || "4" === i ? t.each(function() {
                        element = e(this), "checkbox" == element.prop("type") && element.prev().remove(), element.parents(".field").children("label").find(".required_star").remove(), element.addClass("required").parents(".field").children("label").append('<span class="required_star">*</span>')
                    }) : t.each(function() {
                        if (element = e(this), element.removeClass("required"), element.siblings("label.error").remove(), element.parents(".field").children("label").find(".required_star").remove(), "checkbox" == element.prop("type") && element.prev().attr("name") != element.attr("name")) {
                            var t = FactoryUI.hidden(element.attr("name"), "0");
                            element.before(t), element.parent().siblings("label.error").remove()
                        }
                    })
                }
            }), e(document).on("keyup.companion", "input[rel=companion]", function() {
                selector = e(this).data("companion"), e(this).data("companionEmpty") && e(selector).val(this.value)
            }).on("focus.companion", "input[rel=companion]", function() {
                selector = e(this).data("companion"), e(this).data("companionEmpty", e(selector) && "" === e(selector).val().strip())
            }), sidebarHeight = e("#Sidebar").height(), null !== sidebarHeight && sidebarHeight > e("#Pagearea").height() && e("#Pagearea").css("minHeight", sidebarHeight), hashTabSelect(), e(window).on("hashchange", hashTabSelect), qtipPositions = {
                normal: {
                    my: "center right",
                    at: "center left"
                },
                top: {
                    my: "bottom center",
                    at: "top center"
                },
                bottom: {
                    my: "top center",
                    at: "bottom center"
                },
                left: {
                    my: "top left",
                    at: "bottom  left"
                }
            }, e(".custom-tip, .custom-tip-top, .custom-tip-left, .custom-tip-bottom").on("mouseenter", function(t) {
                config_position = qtipPositions.normal;
                var i = jQuery(this).data("tip-classes") || "";
                e(this).hasClass("custom-tip-top") && (config_position = qtipPositions.top), e(this).hasClass("custom-tip-bottom") && (config_position = qtipPositions.bottom), e(this).hasClass("custom-tip-left") && (config_position = qtipPositions.left), config_position.viewport = jQuery(window), e(this).qtip({
                    overwrite: !1,
                    position: config_position,
                    style: {
                        classes: "ui-tooltip-rounded ui-tooltip-shadow " + i,
                        tip: {
                            mimic: "center"
                        }
                    },
                    show: {
                        event: t.type,
                        ready: !0,
                        delay: 300
                    }
                }, t)
            }).each(function() {
                e.attr(this, "oldtitle", e.attr(this, "title")), this.removeAttribute("title")
            }), menu_box_count = 0, fd_active_drop_box = null, e(".nav-drop .menu-trigger").on("click", function(t) {
                t.preventDefault(), e(this).toggleClass("selected").next().toggle(), e(this).attr("data-menu-name") || e(this, e(this).next()).attr("data-menu-name", "page_menu_" + menu_box_count++), e(this).attr("data-menu-name") !== e(fd_active_drop_box).attr("data-menu-name") && e(fd_active_drop_box).removeClass("selected").next().hide(), fd_active_drop_box = e(this)
            }), e(document).on("click", "[rel=guided-inlinemanual]", function(t) {
                t.preventDefault();
                try {
                    inline_manual_player.activateTopic(e(this).data("topic-id"))
                } catch (i) {}
            }), e(".nav-drop li.menu-item a").bind("click", function() {
                t()
            }), e(document).bind("click", function(i) {
                var n = e(i.target);
                n.parents().hasClass("nav-drop") || t(), n.parent().hasClass("request_form_options") || e("#canned_response_container").hide()
            }), e("body").on("click.helpdesk", "#scroll-to-top", function() {
                e.scrollTo("body")
            }), e(window).on("scroll.select2", function() {
                e(".select2-container.select2-dropdown-open").not(e(this)).select2("positionDropdown")
            }), e(window).on("beforeunload", function(t) {
                var i = e(".form-unsaved-changes-trigger"),
                    n = !1;
                return i.each(function() {
                    n = e(this).data("formChanged") ? !0 : n
                }), n ? (t.preventDefault(), customMessages.confirmNavigate) : void 0
            }), e(".form-unsaved-changes-trigger").on("change", function(t) {
                ["twitter_handle", "tweet_type"].indexOf(e(t.target).attr("id")) > -1 || e(this).data("formChanged", !0)
            }), e("body").on("focus", "select.select2", function(t) {
                e(t.target).select2("focus")
            })
        })
    }(jQuery),
    function(e) {
        e.extend({
            selectall: function(t, i) {
                e(t).on("change", function() {
                    e(t).prop("checked") ? e(i).prop("checked", "checked") : e(i).removeAttr("checked")
                }), e(i).on("change", function() {
                    e(i + ":not(:checked)").length > 0 ? e(t).removeAttr("checked") : e(t).prop("checked", "checked")
                })
            }
        })
    }(jQuery),
    function(e) {
        Helpdesk.can_bind_first = !0, e.fn.bindFirst = function(e, t) {
            this.unbind(e, t), this.bind(e, t);
            var i = this.data("events")[e];
            i.unshift(i.pop()), this.data("events")[e] = i
        }
    }(jQuery),
    function(e) {
        "use strict";
        e(document).ready(function() {
            e(".alert").livequery(function() {
                var t = e(this).not("[rel=permanent]");
                if (t.get(0)) try {
                    closeableFlash(t)
                } catch (i) {}
            }), e(".autolink").livequery(function() {
                e(this).autoLink()
            }), e(".menuselector").livequery(function() {
                e(this).menuSelector()
            }, function() {
                e(this).menuSelector("destroy")
            }), e(".tooltip").livequery(function() {
                e(this).twipsy()
            }), e(".full-width-tooltip").livequery(function() {
                e(this).twipsy({
                    template: '<div class="twipsy-arrow"></div><div class="twipsy-inner big"></div>'
                })
            }), e(".form-tooltip").livequery(function() {
                e(this).twipsy({
                    trigger: "focus",
                    template: '<div class="twipsy-arrow"></div><div class="twipsy-inner big"></div>'
                })
            }), e("a[rel=click-popover-below-left]").livequery(function() {
                e(this).popover({
                    delayOut: 300,
                    trigger: "manual",
                    offset: 5,
                    html: !0,
                    reloadContent: !1,
                    placement: "belowLeft",
                    template: '<div class="dbl_up arrow"></div><div class="hover_card inner"><div class="content ' + e("#" + e(this).attr("data-widget-container")).data("container-class") + '"><div></div></div></div>',
                    content: function() {
                        return e("#" + e(this).attr("data-widget-container")).html()
                    }
                })
            }, function() {
                e(this).popover("destroy")
            }), e("[rel=more-agents-hover]").livequery(function() {
                "undefined" != typeof agentCollisionData && e(this).popover({
                    delayOut: 300,
                    trigger: "manual",
                    offset: 5,
                    html: !0,
                    reloadContent: !1,
                    template: '<div class="dbl_left arrow"></div><div class="hover_card hover-card-agent inner"><div class="content"><div></div></div></div>',
                    content: function() {
                        var e = "agent-info-div",
                            t = "<ul id=" + e + ' class="fc-agent-info">',
                            i = "",
                            n = "";
                        return "undefined" != typeof window.freshchat && freshchat.chatIcon && (i = '<span class="active"><i class="ficon-message"></i></span> <a href="javascript:void(0)" class="tooltip"  title="Begin chat" data-placement="right">', n = "</a>"), agentCollisionData.forEach(function(e) {
                            t += '<li class ="agent_name" id="' + e.userId + '"> <strong>' + i + e.name + n + "</strong></li>"
                        }), t + "</ul>"
                    }
                })
            }, function() {
                e(this).popover("destroy")
            }), e("[rel=contact-hover]").livequery(function() {
                e(this).popover({
                    delayOut: 300,
                    trigger: "manual",
                    offset: 5,
                    html: !0,
                    reloadContent: !1,
                    template: '<div class="dbl_left arrow"></div><div class="hover_card inner"><div class="content"><div></div></div></div>',
                    content: function() {
                        var t = "user-info-div-" + e(this).data("contactId");
                        return jQuery("#" + t).html() || "<div class='sloading loading-small loading-block' id='" + t + "' rel='remote-load' data-url='" + e(this).data("contactUrl") + "'></div>"
                    }
                })
            }, function() {
                e(this).popover("destroy")
            }), e("a[rel=hover-popover-below-left]").livequery(function() {
                e(this).popover({
                    delayOut: 300,
                    offset: 5,
                    trigger: "manual",
                    html: !0,
                    reloadContent: !1,
                    placement: "belowLeft",
                    template: '<div class="dbl_up arrow"></div><div class="hover_card inner"><div class="content ' + e("#" + e(this).attr("data-widget-container")).data("container-class") + '"><p></p></div></div>',
                    content: function() {
                        return e("#" + e(this).attr("data-widget-container")).val()
                    }
                })
            }, function() {
                e(this).popover("destroy")
            }), e("[rel=hover-popover]").livequery(function() {
                e(this).popover({
                    delayOut: 300,
                    trigger: "manual",
                    offset: 5,
                    html: !0,
                    reloadContent: !1,
                    template: '<div class="dbl_left arrow"></div><div class="hover_card inner"><div class="content"><div></div></div></div>',
                    content: function() {
                        return e(this).data("content") || e("#" + e(this).attr("data-widget-container")).val()
                    }
                })
            }, function() {
                e(this).popover("destroy")
            }), e("textarea.autosize").livequery(function() {
                e(this).autosize()
            }), e("[rel=remote-load]").livequery(function() {
                document.getElementById("remote_loaded_dom_elements") || e("<div id='remote_loaded_dom_elements' class='hide' />").appendTo("body");
                var t = jQuery(this);
                e.ajax({
                    type: "GET",
                    url: e(this).data("url"),
                    dataType: "html",
                    success: function(e) {
                        t.html(e), t.attr("rel", ""), t.removeClass("sloading loading-small loading-block"), t.data("loadUnique") || t.clone().prependTo("#remote_loaded_dom_elements"), t.data("extraLoadingClasses") && t.removeClass(t.data("extraLoadingClasses"))
                    }
                })
            }), e("input.datepicker_popover").livequery(function() {
                var t = "yy-mm-dd";
                e(this).data("date-format") && (t = e(this).data("date-format"));
                var i = jQuery(this).clone().removeAttr("class data-date-format"),
                    n = jQuery(this).prop("id");
                if (i.attr("id", "clone_" + n).appendTo(this), jQuery(this).removeAttr("name"), "" == jQuery(this).val() ? jQuery("#" + n).attr("data-initial-val", "empty") : jQuery("#" + n).attr("data-initial-val", jQuery(this).val()), jQuery("#clone_" + n).hide(), jQuery.validator.addClassRules("date", {
                        date: !1
                    }), e(this).datepicker({
                        dateFormat: t,
                        changeMonth: !0,
                        changeYear: !0,
                        altField: "#clone_" + n,
                        altFormat: "yy-mm-dd",
                        beforeShow: function() {
                            Helpdesk.calenderSettings.insideCalendar = !0, Helpdesk.calenderSettings.closeCalendar = !1
                        },
                        onClose: function() {
                            Helpdesk.calenderSettings.closeCalendar = !0
                        },
                        showOn: "both",
                        buttonText: "<i class='ficon-date'></i>"
                    }), "empty" != jQuery("#" + n).data("initial-val")) {
                    var r = jQuery("#clone_" + n).val(),
                        s = new Date(r);
                    jQuery("#" + n).datepicker("setDate", s)
                }
                var a = jQuery(this).siblings(".dateClear");
                0 === a.length && (a = jQuery('<span class="dateClear"><i class="ficon-cross" ></i></div>'), jQuery(this).after(a)), 0 === jQuery(this).val().length && a.hide(), jQuery(this).on("change", function() {
                    0 === jQuery(this).val().length ? a.hide() : a.show()
                }), a.on("click", function() {
                    jQuery(this).siblings("input.date").val(""), jQuery(this).hide(), jQuery("#clone_" + n).val("")
                })
            }), e("input.datetimepicker_popover").livequery(function() {
                e(this).datetimepicker({
                    timeFormat: "HH:mm:ss",
                    dateFormat: "MM dd,yy",
                    changeMonth: !0,
                    changeYear: !0,
                    beforeShow: function() {
                        Helpdesk.calenderSettings.insideCalenda = !0, Helpdesk.calenderSettings.closeCalendar = !1
                    },
                    onClose: function() {
                        Helpdesk.calenderSettings.closeCalendar = !0
                    }
                })
            }), e("[rel=mouse-wheel]").livequery(function() {
                e(this).on("mousewheel DOMMouseScroll", function(t) {
                    t.originalEvent && (t = t.originalEvent);
                    var i = t.wheelDelta || -t.detail;
                    this.scrollTop += (0 > i ? 1 : -1) * parseInt(e(this).data("scrollSpeed")), t.preventDefault()
                })
            }), e("label.overlabel").livequery(function() {
                e(this).overlabel()
            }), e(".nav-trigger").livequery(function() {
                e(this).showAsMenu()
            }), e("input[rel=toggle]").livequery(function() {
                e(this).itoggle()
            }), e("select.select2").livequery(function() {
                var t = {
                    minimumResultsForSearch: 10
                };
                e(this).select2(e.extend(t, e(this).data()))
            }, function() {
                e(this).select2("destroy")
            }), e("input.select2").livequery(function() {
                var t = {
                    tags: [],
                    tokenSeparators: [","],
                    formatNoMatches: function() {
                        return "  "
                    }
                };
                e(this).select2(e.extend(t, e(this).data()))
            }, function() {
                e(this).select2("destroy")
            }), e("div.request_mail").livequery(function() {
                quote_text(this)
            }), e("input.datepicker").livequery(function() {
                e(this).datepicker(e.extend({}, e(this).data(), {
                    dateFormat: getDateFormat("datepicker"),
                    changeMonth: !0,
                    changeYear: !0
                }))
            }), e(".contact_tickets .detailed_view .quick-action").removeClass("dynamic-menu quick-action").attr("title", ""), e(".quick-action.ajax-menu").livequery(function() {
                e(this).showAsDynamicMenu()
            }), e(".quick-action.dynamic-menu").livequery(function() {
                e(this).showAsDynamicMenu()
            }), e(".tourmyapp-toolbar .tourmyapp-next_button").livequery(function() {
                "Next »" == e(this).text() && e(this).addClass("next_button_arrow").text("Next")
            });
            var t;
            e(".image-lazy-load img").livequery(function() {
                t = new Layzr({
                    container: null,
                    selector: ".image-lazy-load img",
                    attr: "data-src",
                    retinaAttr: "data-src-retina",
                    hiddenAttr: "data-layzr-hidden",
                    threshold: 0,
                    callback: function() {
                        e(".image-lazy-load img").css("opacity", 1)
                    }
                })
            }, function() {
                t._destroy()
            }), e("ul.ui-form, .cnt").livequery(function() {
                e(this).not(".dont-validate").parents("form:first").validate()
            }), e("div.ui-form").livequery(function() {
                e(this).not(".dont-validate").find("form:first").validate()
            }), e("form.ui-form").livequery(function() {
                e(this).not(".dont-validate").validate()
            });
            var i = {};
            i.submitHandler = function(t, i) {
                e(i).button("loading"), e(t).data("remote") ? e(t).ajaxSubmit({
                    dataType: "script",
                    success: function(n) {
                        e(i).button("reset"), e("#" + e(t).data("update")).html(n)
                    }
                }) : setTimeout(function() {
                    add_csrf_token(t), e(t).data("formChanged", !1), t.submit()
                }, 50)
            }, e("form[rel=validate]").livequery(function() {
                e(this).validate(e.extend(i, e(this).data()))
            }), e("#Activity .activity > a").livequery(function() {
                e(this).attr("data-pjax", "#body-container")
            }), e('[rel="select-choice"]').livequery(function() {
                var t = e(this).data("maxSelectionSize") || 10;
                jQuery(this).select2({
                    maximumSelectionSize: t,
                    removeOptionOnBackspace: !1
                });
                var i = e(this).siblings(".select2-container"),
                    n = e(this).data("disableField");
                n = n.split(","), i.find(".select2-search-choice div").each(function(t, i) {
                    var r = jQuery(i).text(); - 1 != e.inArray(r, n) && jQuery(i).next("a").remove()
                })
            }, function() {
                e(this).select2("destroy")
            }), e("[rel=sticky]").livequery(function() {
                var t = e(this).data("scrollTop");
                e(this).sticky(), e(this).on("sticky_kit:stick", function() {
                    t && (e("#scroll-to-top").length || e(this).append("<i id='scroll-to-top'></i>"), e("#scroll-to-top").addClass("visible"))
                }).on("sticky_kit:unstick", function() {
                    t && e("#scroll-to-top").removeClass("visible")
                })
            }), e(".btn-collapse").livequery(function() {
                e(this).collapseButton()
            }), e("[rel='image-enlarge'] img").livequery(function() {
                var t = e(this);
                e("<img/>").attr("src", t.attr("src")).on("load", function() {
                    var i = this.width,
                        n = this.height,
                        r = e(t).actual("width"),
                        s = e(t).actual("height"),
                        a = i / n,
                        o = r / s;
                    o !== a && (t.outerHeight(r / a), t.parent("a").get(0) || t.wrap(function() {
                        return "<a target='_blank' class='image-enlarge-link' href='" + this.src + "'/>"
                    }))
                })
            }), e("[rel=remote-tag]").livequery(function() {
                var t = [],
                    i = e(this),
                    n = {
                        multiple: !0,
                        minimumInputLength: 1,
                        maximumInputLength: 32,
                        data: t,
                        quietMillis: 500,
                        ajax: {
                            url: "/search/autocomplete/tags",
                            dataType: "json",
                            data: function(e) {
                                return {
                                    q: e
                                }
                            },
                            results: function(e) {
                                var t = [];
                                return jQuery.each(e.results, function(e, i) {
                                    var n = escapeHtml(i.value);
                                    t.push({
                                        id: n,
                                        text: n
                                    })
                                }), {
                                    results: t
                                }
                            }
                        },
                        initSelection: function(e, i) {
                            i(t)
                        },
                        formatInputTooShort: function(e, t) {
                            return I18n.t("validation.select2_minimum_limit", {
                                char_count: t - e.length
                            })
                        },
                        formatInputTooLong: function() {
                            return MAX_TAG_LENGTH_MSG
                        }
                    };
                0 != i.data("allowCreate") && (n.createSearchChoice = function(t, i) {
                    return 0 === e(i).filter(function() {
                        return 0 === this.text.localeCompare(t)
                    }).length ? {
                        id: t,
                        text: t
                    } : void 0
                }), i.val().split(",").each(function(e) {
                    t.push({
                        id: e,
                        text: e
                    })
                }), i.select2(n)
            }, function() {
                e(this).select2("destroy")
            })
        })
    }(jQuery), window.App = window.App || {},
    function() {
        "use strict";
        App = {
            namespace: ""
        }
    }(window.jQuery), Ajax.Base.prototype.initialize = Ajax.Base.prototype.initialize.wrap(function(e, t) {
        var i = t.requestHeaders || {},
            n = $$('meta[name="csrf-token"]')[0];
        return void 0 != n && (i["X-CSRF-Token"] = n.getAttribute("content"), t.requestHeaders = i), e(t)
    }), jQuery.ajaxSetup({
        headers: {
            "X-CSRF-Token": jQuery('meta[name="csrf-token"]').attr("content")
        }
    }), window.add_csrf_token = function(e) {
        var t = jQuery('meta[name="csrf-token"]').attr("content") || null;
        t && !jQuery(e).find("[name=authenticity_token]").get(0) && jQuery(e).append("<input type='hidden' name='authenticity_token' value='" + t + "' />")
    }, jQuery("form").livequery(function() {
        add_csrf_token(this)
    }), ! function(e) {
        "use strict";
        window.Fjax = {
            beforeNextPage: null,
            afterNextPage: null,
            unload: null,
            pjax_traversal_count: 0,
            pjax_traversal_limit: 10,
            bodyClass: null,
            _prevAfterNextPage: null,
            _prevBodyClass: null,
            callBeforeSend: function(t, i) {
                var n = e(".form-unsaved-changes-trigger"),
                    r = !1;
                if (n.each(function() {
                        r = e(this).data("formChanged") ? !0 : r
                    }), r) {
                    var s = confirm(customMessages.confirmNavigate);
                    if (!s) return i.abort(), !1
                }
                return this._SocketCleanUp(), this._deleteDetachedDOM(), e.xhrPool_Abort(), this._triggerUnload() === !1 ? !1 : (this._beforeSendCleanup(), "function" == typeof this.beforeNextPage && this.beforeNextPage() === !1 ? !1 : (this.beforeNextPage = null, "function" == typeof this.afterNextPage && (this._prevAfterNextPage = this.afterNextPage), this.afterNextPage = null, this.bodyClass && (this._prevBodyClass = this.bodyClass), this.bodyClass = null, this._setLoading(), !0))
            },
            callBeforeReplace: function(t, i, n) {
                this._beforeSendExtras(t, n), e(n.target).data("twipsy", ""), "function" == typeof this._prevAfterNextPage && this._prevAfterNextPage(), this._prevAfterNextPage = null, Fjax.current_page = "", e("[data-keybinding]").expire(), "undefined" != typeof soundManager && soundManager.stopAll(), e(window).unbind(".pageless")
            },
            pjaxLimitExceeded: function() {
                return !(this.pjax_traversal_count >= this.pjax_traversal_limit) || window.freshfonecalls && window.freshfonecalls.tConn ? !1 : !0
            },
            callAfterReceive: function() {
                this._removeLoading(), this._afterReceiveCleanup();
                var t = e(t);
                this._prevBodyClass != this.bodyClass && t.removeClass(this._prevBodyClass).addClass(this.bodyClass), this._prevBodyClass = null, e("body").trigger("pjaxDone")
            },
            callAtEnd: function() {
                "function" == typeof this.end && this.end(), this.end = null
            },
            _setLoading: function() {
                var e = Math.round(2 * Math.random() + 0);
                switch (e) {
                    case 0:
                        NProgress.set(0), NProgress.set(.4), NProgress.set(.6), NProgress.set(.8);
                        break;
                    case 1:
                        NProgress.set(0), NProgress.set(.3), NProgress.set(.5), NProgress.set(.7), NProgress.set(.9);
                        break;
                    case 2:
                        NProgress.set(0), NProgress.set(.4), NProgress.set(.5), NProgress.set(.6), NProgress.set(.7), NProgress.set(.8), NProgress.set(.9)
                }
            },
            resetLoading: function() {
                NProgress.remove()
            },
            _triggerUnload: function() {
                if ("function" == typeof this.unload) {
                    var e = this.unload();
                    if (this.unload = null, "string" == typeof e) return e += "\n\n Are you sure you want to leave this page?", confirm(e)
                }
            },
            _removeLoading: function() {
                NProgress.done(), setTimeout(NProgress.remove, 500)
            },
            _beforeSendExtras: function(t, i) {
                new Date, e("#body-container").height(), e("ul.header-tabs li.active").removeClass("active"), this._initParallelRequest(e(t.relatedTarget), i.data)
            },
            callAfterRecieve: function(t, i, n) {
                Fjax.callAfterReceive(), jQuery("#header_search").blur(), "function" == typeof window.pjaxPrevUnload && window.pjaxPrevUnload(), window.pjaxPrevUnload = null, Fjax.callAtEnd();
                var r = jQuery(document).data();
                jQuery(document).data("requestDone", !0), r.parallelData && e(t.relatedTarget).data() ? e(e(t.relatedTarget).data().parallelPlaceholder).html(r.parallelData) : r.parallelData && n.data && e(n.data.parallelPlaceholder).html(r.parallelData)
            },
            _beforeSendCleanup: function() {
                e("#cf_cache").remove(), e("#response_dialog").remove(), e(".ui-dialog").remove(), e("#bulkcontent").remove(), this._disconnectNode()
            },
            _afterReceiveCleanup: function() {
                e(".popover").remove(), e(".modal:not(.persistent_modal), .modal-backdrop").remove(), e(".twipsy").remove()
            },
            _SocketCleanUp: function() {
                window.node_socket && (window.node_socket.disconnect(), e("[data-note-type]").off("click.agent_collsion"), e(".reply_agent_collision").off("click.agent_collsion"))
            },
            _deleteDetachedDOM: function() {
                delete e("#TicketProperties select.dropdown, #TicketProperties select.dropdown_blank, #TicketProperties select.nested_field").prevObject, delete e("body.ticket_details [rel=tagger]").prevObject, delete e("[data-hotkey]").prevObject, delete e("a.page-btn.next_page.btn.tooltip").prevObject, delete e("#body-container").prevObject
            },
            _disconnectNode: function() {
                try {
                    jQuery(document).trigger("disconnectNode")
                } catch (e) {
                    console.log("Unable to disconnect the socket connection"), console.log("Error:"), console.log(e)
                }
            },
            success: function() {
                this.pjax_traversal_count = this.pjax_traversal_count + 1, window.history.state.body_class = e("body").attr("class"), window.history.replaceState(window.history.state, "for_pjax"), this.pjaxLimitExceeded() && (jQuery.pjax.disable(), jQuery(document).off("click.pjax", "a[data-pjax]"))
            },
            _initParallelRequest: function(e, t) {
                if (jQuery(document).data("requestDone", !1), jQuery(document).data("parallelData", void 0), e.data("parallelUrl") || t) {
                    var i;
                    i = e.data("parallelUrl") ? e.data() : t, void 0 !== i.parallelUrl && jQuery.get(i.parallelUrl, function(e) {
                        jQuery(document).data("requestDone") ? jQuery(i.parallelPlaceholder).html(e) : jQuery(document).data("parallelData", e)
                    })
                }
            }
        }, e.browser.msie || e.browser.msedge || e(document).pjax("a[data-pjax]", {
            timeout: -1,
            push: !0,
            maxCacheLength: 0,
            replace: !1
        }).bind("pjax:beforeSend", function(e, t, i, n) {
            return Fjax.callBeforeSend(e, t, i, n)
        }).bind("pjax:beforeReplace", function(e, t, i) {
            Fjax.callBeforeReplace(e, t, i)
        }).bind("pjax:end", function(e, t, i) {
            return Fjax.callAfterRecieve(e, t, i), !0
        }).bind("pjax:success", function() {
            Fjax.success()
        });
        var t = {
            timeout: -1,
            push: !0,
            maxCacheLength: 0,
            replace: !1,
            container: "#body-container"
        };
        window.pjaxify = function(i) {
            return e.browser.msie || e.browser.msedge ? window.location = i : (e.pjax(e.extend({}, t, {
                url: i
            })), void 0)
        }
    }(window.jQuery), window.App = window.App || {}, window.Fjax = window.Fjax || {},
    function() {
        "use strict";
        Fjax.Config = {
            paths: {
                "/discussions": "discussions",
                "/solution": "solutions",
                "/admin": "admin",
                "/account/update_languages": "admin",
                "/search": "search",
                "/phone/call_history": "freshfonecallhistory",
                "/phone/dashboard": "freshfonedashboard",
                "/reports/phone/summary_reports": "freshfonereports",
                "/reports/v2": "helpdeskreports",
                "/contacts": "contacts",
                "/users": "contacts",
                "/companies": "companies",
                "/helpdesk/tickets/archived/": "archiveticketdetails",
                "/helpdesk/tickets": "tickets",
                "/helpdesk/ticket_templates": "parentchildtemplates",
                "/helpdesk/parent_template": "parentchildtemplates",
                "/helpdesk/dashboard/agent_status": "freshfoneagents",
                "/helpdesk/tickets/compose_email": "tickets",
                "/helpdesk/dashboard/unresolved_tickets": "unresolvedtickets",
                "/helpdesk": "realtime_dashboard"
            },
            LOADING_WAIT: 60
        }
    }(window.jQuery),
    function(e) {
        "use strict";
        Fjax.Callbacks = {
            init: function() {},
            codemirror: function() {
                e("[rel=codemirror]").livequery(function() {
                    e(this).codemirror(e(this).data("codemirrorOptions"))
                })
            },
            colorpicker: function() {},
            shortcut: function() {}
        }
    }(window.jQuery),
    function(e) {
        "use strict";
        Fjax.Assets = {
            loaded: {
                app: [],
                plugins: [],
                integrations: []
            },
            javascripts: {},
            stylesheets: {},
            setup: function(e, t, i) {
                this.javascripts = e, this.stylesheets = t, this.host_url = i
            },
            isJSNeeded: function(e, t) {
                return t = t || "app", "undefined" != typeof this.javascripts[t][e]
            },
            isCSSNeeded: function(e, t) {
                return t = t || "app", "undefined" != typeof this.stylesheets[t][e]
            },
            alreadyLoaded: function(e, t) {
                return t = t || "app", this.loaded[t].indexOf(e) > -1
            },
            serve: function(e, t) {
                if (t = t || function() {}, !this.isJSNeeded(e) && !this.isCSSNeeded(e)) return !1;
                if (this.isJSNeeded(e)) {
                    var i = this;
                    $LAB.script(this.javascripts.app[e]).wait(function() {
                        t(), i.loaded.app.push(e)
                    })
                }
                this.isCSSNeeded(e) && this.load_css(e)
            },
            load_css: function(t) {
                t = "string" == typeof t ? [t] : t;
                var i = this;
                e.each(t, function(t, n) {
                    e("<link/>", {
                        rel: "stylesheet",
                        type: "text/css",
                        href: i.host_url + "/assets/" + n
                    }).appendTo("head")
                })
            },
            plugin: function(e) {
                var t = this;
                this.alreadyLoaded(e, "plugins") || ($LAB.script(this.javascripts.plugins[e]).wait(function() {
                    t.loaded.plugins.push(e), "function" == typeof Fjax.Callbacks[e] && Fjax.Callbacks[e]()
                }), this.isCSSNeeded(e, "plugins") && this.load_css(this.stylesheets.plugins[e]))
            },
            integration: function(e, t) {
                var i = this;
                "function" != typeof t && (t = function() {}), this.alreadyLoaded(e, "integrations") ? t() : $LAB.script(this.javascripts.integrations[e]).wait(function() {
                    i.loaded.integrations.push(e), t()
                })
            }
        }
    }(window.jQuery), String.prototype.asClassName = function() {
        "use strict";
        return this.toLowerCase().split("_").collect(function(e) {
            return e.charAt(0).toUpperCase() + e.substring(1)
        }).join("")
    }, window.App = window.App || {}, window.Fjax = window.Fjax || {},
    function(e) {
        "use strict";
        Fjax.Manager = {
            current: "",
            previous: "",
            visited: [],
            start: function() {
                this.bindPjaxListeners(), this.loadReqdAssets(), e(function() {
                    Fjax.Manager.loadedPage()
                })
            },
            loadReqdAssets: function(e) {
                e = e || window.location.pathname;
                var t = this.assetForPath(e);
                return this.previous = this.current, t ? (this.current = t, Fjax.Assets.serve(t), void 0) : (this.current = "", void 0)
            },
            className: function(e) {
                return e.asClassName()
            },
            assetForPath: function(e) {
                var t;
                for (t in Fjax.Config.paths)
                    if (Fjax.Config.paths.hasOwnProperty(t) && e.startsWith(t)) return Fjax.Config.paths[t];
                return !1
            },
            bindPjaxListeners: function() {
                var t = this;
                e(document).bind("pjax:beforeSend", function(e, i, n, r) {
                    t.loadReqdAssets(r.url.replace(/^.*\/\/[^\/]+/, ""))
                }), e(document).bind("pjax:beforeReplace", function() {
                    t.leavePage()
                }), e(document).bind("pjax:end", function() {
                    t.loadedPage()
                })
            },
            loadedPage: function() {
                var t, i = this;
                "" !== this.current && (this.loadedCurrent() ? (e("body").trigger("assetLoaded.fjax"), this.alreadyVisited(this.current) ? this.onVisit() : this.onFirstVisit()) : t = setInterval(function() {
                    this.count = this.count || 1, i.loadedCurrent() ? (clearInterval(t), e("body").trigger("assetLoaded.fjax"), i.onFirstVisit()) : this.count > 10 * Fjax.Config.LOADING_WAIT && (clearInterval(t), console.log("Error trying to load ", i.current), i.current = ""), this.count += 1
                }, 100), this.visited.push(this.current), this.previous = "")
            },
            leavePage: function() {
                "" !== this.previous && this.onLeave()
            },
            loadedCurrent: function() {
                return "" === this.current ? !0 : Fjax.Assets.alreadyLoaded(this.current)
            },
            alreadyVisited: function(e) {
                return this.visited.indexOf(e) > -1
            },
            onFirstVisit: function() {
                App[this.className(this.current)].onFirstVisit()
            },
            onVisit: function() {
                App[this.className(this.current)].onVisit()
            },
            onLeave: function() {
                App[this.className(this.previous)].onLeave()
            }
        }
    }(window.jQuery), jQuery.noConflict(), ! function(e) {
        e(function() {
            "use strict";
            e(document).on("click", "a[data-show-dom], button[data-show-dom]", function(t) {
                t.preventDefault(), e(this).data("remote") || e(e(this).data("showDom")).show()
            }), e(document).on("click", "a[data-hide-dom], button[data-hide-dom]", function(t) {
                t.preventDefault(), e(this).data("remote") || e(e(this).data("hideDom")).hide()
            }), e(document).on("click", "a[data-toggle-dom], button[data-toggle-dom]", function(t) {
                t.preventDefault(), e(this).data("remote") || (void 0 != e(this).data("animated") ? e(e(this).data("toggleDom")).slideToggle() : e(e(this).data("toggleDom")).toggle())
            }), e(document).on("click", "[data-toggle-text]", function(t) {
                if (t.preventDefault(), !e(this).data("remote")) {
                    var i = e(this).data("toggleText"),
                        n = e(this).html();
                    e(this).data("toggleText", n).html(i)
                }
            }), e(document).on("click", "input[data-proxy-for], a[data-proxy-for]", function(t) {
                var i = e(this).data("proxyFor");
                if ("a" == this.nodeName.toLowerCase()) return jQuery("input[data-proxy-for=" + i + "]").trigger("click"), void 0;
                t.preventDefault(), e(this).hide();
                var n = e(i).show().find("textarea");
                n.getEditor() && n.getEditor().focus()
            })
        })
    }(window.jQuery);
var NavSearchUtils = NavSearchUtils || function() {
    function e(e) {
        var t = [];
        return t.push = function() {
            return t.length == e && t.shift(), Array.prototype.push.apply(this, arguments)
        }, t
    }

    function t() {
        return "local_recent_searches_" + window.current_account_id + "_" + window.current_user_id
    }

    function i() {
        return "local_recent_tickets_" + window.current_account_id + "_" + window.current_user_id
    }

    function n() {
        return "local_recent_searches_tickets_timestamp_" + window.current_account_id + "_" + window.current_user_id
    }
    var r = {};
    return r.localRecentSearchKey = t(), r.localRecentTicketKey = i(), r.localRecentTimestampKey = n(), r.tryClearLocalRecents = function() {
        var e = getFromLocalStorage(r.localRecentTimestampKey),
            t = (new Date).getTime(),
            i = 28800;
        if (e) {
            var n = t - e;
            n = Math.round(n / 1e3), n > i && (r.clearLocalRecents(), storeBrowserLocalStorage(r.localRecentTimestampKey, t))
        } else r.clearLocalRecents(), storeBrowserLocalStorage(r.localRecentTimestampKey, t)
    }, r.clearLocalRecents = function() {
        removeFromLocalStorage(r.localRecentSearchKey), removeFromLocalStorage(r.localRecentTicketKey)
    }, r.clearLocalRecentTimestamp = function() {
        removeFromLocalStorage(r.localRecentTimestampKey)
    }, r.saveServerRecents = function(t, i) {
        var n = e(5);
        if (t)
            for (var s = 0; s < t.length; s++) {

                var a = t[s];
                i ? a = a.length > 100 ? a.substring(0, 100) + "..." : a : a.subject = a.subject.length > 100 ? a.subject.substring(0, 100) + "..." : a.subject, n.push(a)
            }
        i ? (r.localRecentSearches = n, r.setLocalRecentSearches(r.localRecentSearchKey)) : (r.localRecentTickets = n, r.setLocalRecentTickets(r.localRecentTicketKey))
    }, r.saveToLocalRecentSearches = function(e) {
        r.localRecentSearches = r.getLocalRecentSearches(r.localRecentSearchKey);
        var t = !1,
            i = e.replace(/^\s+|\s+$/g, "").toLowerCase();
        if (0 !== i.length) {
            for (var n = 0; n < r.localRecentSearches.length; n++) {
                var s = r.localRecentSearches[n].replace(/^\s+|\s+$/g, "").toLowerCase();
                if (t = s == i) {
                    r.localRecentSearches.splice(n, 1), r.localRecentSearches.splice(4, 0, e);
                    break
                }
            }
            t || r.localRecentSearches.push(e), r.setLocalRecentSearches(r.localRecentSearchKey)
        }
    }, r.getLocalRecentSearches = function(t) {
        var i = e(5),
            n = getFromLocalStorage(t);
        if (!n) return i;
        for (var r = 0; r < n.length; r++) i.push(n[r]);
        return i
    }, r.setLocalRecentSearches = function(e) {
        storeBrowserLocalStorage(e, r.localRecentSearches)
    }, r.deleteRecentTicketById = function(e) {
        if (e) {
            r.localRecentTickets = r.getLocalRecentTickets(r.localRecentTicketKey);
            for (var t = 0; t < r.localRecentTickets.length; t++)
                if (r.localRecentTickets[t].displayId == e) {
                    r.localRecentTickets.splice(t, 1), r.setLocalRecentTickets(r.localRecentTicketKey);
                    break
                }
        }
    }, r.saveToLocalRecentTickets = function(e) {
        if (1 != e.ticket_deleted && 1 != e.ticket_spam) {
            var t = !1;
            r.localRecentTickets = r.getLocalRecentTickets(r.localRecentTicketKey);
            for (var i = 0; i < r.localRecentTickets.length; i++)
                if (t = r.localRecentTickets[i].displayId == e.displayId) {
                    r.localRecentTickets.splice(i, 1), r.localRecentTickets.splice(4, 0, {
                        displayId: e.displayId,
                        subject: e.ticket_subject,
                        path: e.ticket_path
                    });
                    break
                }
            t || r.localRecentTickets.push({
                displayId: e.displayId,
                subject: e.ticket_subject,
                path: e.ticket_path
            }), r.setLocalRecentTickets(r.localRecentTicketKey)
        }
    }, r.getLocalRecentTickets = function(t) {
        var i = e(5),
            n = getFromLocalStorage(t);
        if (!n) return i;
        for (var r = 0; r < n.length; r++) i.push(n[r]);
        return i
    }, r.setLocalRecentTickets = function(e) {
        storeBrowserLocalStorage(e, r.localRecentTickets)
    }, r
}();
jQuery(document).ready(function() {
        function e(e) {
            jQuery.ajax({
                url: "/search/recent_searches_tickets",
                dataType: "json",
                success: function(t) {
                    NavSearchUtils.saveServerRecents(t.recent_searches, !0), NavSearchUtils.saveServerRecents(t.recent_tickets, !1), e()
                }
            })
        }

        function t() {
            if (NavSearchUtils.localRecentSearches.length > 0) {
                jQuery(".recent_searches_li").remove();
                for (var e = NavSearchUtils.localRecentSearches.length - 1; e > -1; e--) {
                    var t = JST["app/search/templates/spotlight_result_recent_search"]({
                        id: e,
                        path: "/search/all?term=" + encodeURIComponent(NavSearchUtils.localRecentSearches[e]),
                        content: escapeHtml(NavSearchUtils.localRecentSearches[e])
                    });
                    jQuery("#SearchResultsBar .recent_searches_results").append(t)
                }
            }
            if (NavSearchUtils.localRecentTickets.length > 0) {
                jQuery(".recent_tickets_li").remove();
                for (var e = NavSearchUtils.localRecentTickets.length - 1; e > -1; e--) {
                    var t = JST["app/search/templates/spotlight_result_recent_ticket"]({
                        id: e,
                        displayId: NavSearchUtils.localRecentTickets[e].displayId,
                        path: NavSearchUtils.localRecentTickets[e].path,
                        subject: escapeHtml(NavSearchUtils.localRecentTickets[e].subject)
                    });
                    jQuery("#SearchResultsBar .recent_tickets_results").append(t)
                }
            }
            $J("#SearchResultsBar").css("display", "inline"), jQuery("ul.results").filter(function() {
                return 0 == jQuery(this).find("li.spotlight_result").length
            }).hide(), jQuery("ul.results").filter(function() {
                return jQuery(this).find("li.spotlight_result").length > 0
            }).show()
        }
        var n, r = -1,
            s = !1,
            a = !0,
            o = "",
            l = "";
        NavSearchUtils.tryClearLocalRecents(), callbackToSearch = function(e, t) {
            jQuery("#SearchBar").addClass("sloading loading-small loading-right"), jQuery(".results").hide().find("li.spotlight_result").remove(), jQuery.ajax({
                url: t + e,
                dataType: "json",
                success: function(t) {
                    e == encodeURIComponent($("header_search").value) && (jQuery("#SearchResultsBar").css("display", "inline"), r = -1, appendResults(t)), jQuery("#SearchBar").removeClass("sloading loading-small loading-right")
                }
            })
        }, appendResults = function(e) {
            var t = {},
                n = e.results;
            for (0 == n.length && jQuery(".results_info").html('<li class="spotlight_result"><div>' + e.no_results_text + "</div></li>"), i = 0; 15 > i; i++) {
                var r = n[i];
                if (r) {
                    var s = r.result_type;
                    t[s] = "user" == s ? ("undefined" == typeof t[s] ? "" : t[s]) + JST["app/search/templates/spotlight_result_user"](r) : ("undefined" == typeof t[s] ? "" : t[s]) + JST["app/search/templates/spotlight_result"](r)
                }
            }
            for (var a in t) jQuery("#SearchResultsBar ." + a + "_results").append(t[a]);
            if (e.more_results_text || n.length > 30) {
                var o = '<a href="/search/all?term=' + e.term + '">' + e.more_results_text + "</a>";
                jQuery(".results_info").html('<li class="spotlight_result">' + o + "</li>")
            }
            jQuery("ul.results").filter(function() {
                return jQuery(this).find("li.spotlight_result").length > 0
            }).show(), jQuery("ul.results").on("click.add_to_recent_search", "li.spotlight_result a", function(e) {
                jQuery(e.target).hasClass("spotlight-result-user-ticket-icon") || NavSearchUtils.saveToLocalRecentSearches(l)
            })
        };
        var c = function() {
                var e = encodeURIComponent(jQuery("#header_search").val()),
                    t = jQuery(".nav_search").attr("action") + "?term=" + e;
                window.pjaxify(t)
            },
            d = function() {
                $J("#SearchResultsBar").hide(), $J("#SearchBar").removeClass("active"), $J("#header_search").attr("placeholder", ""), $J("#header_search").val(""), o = "", $J("ul.results li.spotlight_result").remove(), jQuery("ul.results").filter(function() {
                    return 0 == jQuery(this).find("li.spotlight_result").length
                }).hide()
            };
        $J(document).on("mouseenter mouseleave", "#SearchResultsBar a", function() {
            $J(n).removeClass("active"), n = $J(this).addClass("active")
        }), $J(document).on("click", "#SearchResultsBar a", function() {
            d(), s = !1
        }), $J(document).on("mouseenter", "#SearchResultsBar", function() {
            a = !1, s = !0
        }), $J(document).on("mouseleave", "#SearchResultsBar", function() {
            s = !1, $J("#SearchResultsBar a").removeClass("active"), n = null
        }), $J(document).on("focusout", "#header_search", function() {
            a = !1, s || d()
        }), $J(document).on("focusin", "#header_search", function() {
            a = !0, searchString = this.value.replace(/^\s+|\s+$/g, ""), $J("#SearchBar").addClass("active"), $J("#header_search").attr("placeholder", "Search"), $J("#SearchBar").twipsy("hide"), NavSearchUtils.localRecentSearches = NavSearchUtils.getLocalRecentSearches(NavSearchUtils.localRecentSearchKey), NavSearchUtils.localRecentTickets = NavSearchUtils.getLocalRecentTickets(NavSearchUtils.localRecentTicketKey), 0 == NavSearchUtils.localRecentSearches.length || 0 == NavSearchUtils.localRecentTickets.length ? delay(function() {
                jQuery("#SearchBar").addClass("sloading loading-small loading-right"), e(function() {
                    jQuery("#SearchBar").removeClass("sloading loading-small loading-right"), t()
                })
            }, 100) : t(), "" != searchString && jQuery("#SearchResultsBar li").hasClass("spotlight_result") && $J("#SearchResultsBar").css("display", "inline")
        }), jQuery(document).on("click.remove_recent_search", ".recent_search_cross_icon", function(e) {
            e.stopPropagation(), e.preventDefault();
            var t = jQuery(e.currentTarget).parents("li.spotlight_result").attr("id"),
                i = parseInt(t.replace("recent_search_", "")),
                n = NavSearchUtils.localRecentSearches[i];
            NavSearchUtils.localRecentSearches.splice(i, 1), NavSearchUtils.setLocalRecentSearches(NavSearchUtils.localRecentSearchKey), jQuery.post("/search/remove_recent_search", {
                search_key: n
            }), 0 === i && 0 === NavSearchUtils.localRecentSearches.length && (jQuery(e.currentTarget).parents("#recent_search_" + i).remove(), jQuery("ul.results").filter(function() {
                return 0 == jQuery(this).find("li.spotlight_result").length
            }).hide()), jQuery("#header_search").focus()
        }), jQuery(document).on("click.search_open_user_tickets", ".spotlight-result-user-ticket-icon", function(e) {
            e.stopPropagation(), e.preventDefault(), jQuery(e.currentTarget).parents("li.spotlight_result").find("a.spotlight-result-hidden-user-tickets-link").click()
        });
        var u = function(e) {
                searchlist = $J("#SearchResultsBar a");
                for (var t = 0; t < searchlist.length; t++)
                    if (jQuery(searchlist[t]).hasClass("active")) {
                        r = t;
                        break
                    }
                $J(n).removeClass("active"), r = Math.min(searchlist.size() - 1, Math.max(0, r + e)), n = $J(searchlist.get(r)).addClass("active")
            },
            h = function(e) {
                switch (a = !1, e) {
                    case 40:
                        u(1);
                        break;
                    case 38:
                        u(-1);
                        break;
                    case 13:
                        a = !0, $J(n).trigger("click")
                }
            },
            p = function(e, t) {
                search_url = "/search/home/suggest?term=", "" != e && e.length > 1 && o != e ? delay(function() {
                    l = t.value, callbackToSearch(encodeURIComponent(e), search_url), o = e
                }, 450) : o == e ? "" != e && jQuery("#SearchResultsBar li").hasClass("spotlight_result") && ($J("#SearchResultsBar").css("display", "inline"), $J("ul.results").filter(function() {
                    return jQuery(this).find("li.spotlight_result").length > 0
                }).show()) : jQuery("#SearchResultsBar").hide()
            };
        $J("#header_search").bind("keyup", function(e) {
            switch (e.keyCode) {
                case 40:
                case 38:
                case 13:
                    h(e.keyCode), e.preventDefault();
                    break;
                default:
                    var t = this;
                    searchString = t.value.replace(/^\s+|\s+$/g, ""), p(searchString, t)
            }
        }), $J("#header_search").on("paste", function(e) {
            if (!e.keyCode) {
                var t = this;
                setTimeout(function() {
                    searchString = t.value.replace(/^\s+|\s+$/g, ""), p(searchString, t)
                }, 100)
            }
        }), jQuery("body").on("submit", ".nav_search", function(e) {
            return a ? (e.preventDefault(), c(), void 0) : !1
        }), jQuery(document).on("click.remove_recent_ticket", "[data-domhelper-name='ticket-delete-btn'], li a.spam", function() {
            TICKET_DETAILS_DATA && NavSearchUtils.deleteRecentTicketById(TICKET_DETAILS_DATA.displayId)
        }), jQuery(document).on("click.clear_local_recents", ".signout_link", function() {
            NavSearchUtils.clearLocalRecentTimestamp(), NavSearchUtils.clearLocalRecents()
        })
    }),
    function() {
        this.JST || (this.JST = {}), this.JST["app/search/templates/spotlight_result"] = function(obj) {
            var __p = [];
            with(obj || {}) __p.push('<li class="spotlight_result">\n	<a href="', path, '" data-pjax="#body-container">\n		<i class="', result_type, '_icon"></i>\n		', content, "\n	</a>  \n</li>\n");
            return __p.join("")
        }
    }.call(this),
    function() {
        this.JST || (this.JST = {}), this.JST["app/search/templates/spotlight_result_user"] = function(obj) {
            var __p = [];
            with(obj || {}) __p.push('<li class="spotlight_result">\n  <a href="', path, '" data-pjax="#body-container">\n    <span class="pull-right" title="', lang.viewAllTickets, '">\n      <i class="ficon-ticket ficon-16 spotlight-result-user-ticket-icon"></i>\n    </span>\n    <i class="user_icon"></i>      \n        ', content, '                \n  </a>\n  <a href="/helpdesk/tickets/filter/requester/', id, '" data-parallel-url="/helpdesk/tickets/filter_options?filter_name=requester&requester_id=', id, '" class="spotlight-result-hidden-user-tickets-link" data-parallel-placeholder="#ticket-leftFilter" data-pjax="#body-container"></a>   \n</li>\n');
            return __p.join("")
        }
    }.call(this),
    function() {
        this.JST || (this.JST = {}), this.JST["app/search/templates/spotlight_result_recent_search"] = function(obj) {
            var __p = [];
            with(obj || {}) __p.push('<li id="recent_search_', id, '" class="spotlight_result recent_searches_li">\n	<a href="', path, '" data-pjax="#body-container">\n		<i class="recent_search_icon"></i>\n		', content, '		\n		<i class="pull-right recent_search_cross_icon"></i>		\n	</a>  \n</li>\n');
            return __p.join("")
        }
    }.call(this),
    function() {
        this.JST || (this.JST = {}), this.JST["app/search/templates/spotlight_result_recent_ticket"] = function(obj) {
            var __p = [];
            with(obj || {}) __p.push('<li id="recent_ticket_', id, '" class="spotlight_result recent_tickets_li">\n	<a href="', path, '" data-pjax="#body-container">\n		<i class="helpdesk_ticket_icon"></i>\n		', subject, " (#", displayId, ")\n	</a>  \n</li>\n");
            return __p.join("")
        }
    }.call(this),
    /**
     * @version: 1.0 Alpha-1
     * @author: Coolite Inc. http://www.coolite.com/
     * @date: 2008-05-13
     * @copyright: Copyright (c) 2006-2008, Coolite Inc. (http://www.coolite.com/). All rights reserved.
     * @license: Licensed under The MIT License. See license.txt and http://www.datejs.com/license/. 
     * @website: http://www.datejs.com/
     */
    Date.CultureInfo = {
        name: "en-US",
        englishName: "English (United States)",
        nativeName: "English (United States)",
        dayNames: ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
        abbreviatedDayNames: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
        shortestDayNames: ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa"],
        firstLetterDayNames: ["S", "M", "T", "W", "T", "F", "S"],
        monthNames: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
        abbreviatedMonthNames: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
        amDesignator: "AM",
        pmDesignator: "PM",
        firstDayOfWeek: 0,
        twoDigitYearMax: 2029,
        dateElementOrder: "mdy",
        formatPatterns: {
            shortDate: "M/d/yyyy",
            longDate: "dddd, MMMM dd, yyyy",
            shortTime: "h:mm tt",
            longTime: "h:mm:ss tt",
            fullDateTime: "dddd, MMMM dd, yyyy h:mm:ss tt",
            sortableDateTime: "yyyy-MM-ddTHH:mm:ss",
            universalSortableDateTime: "yyyy-MM-dd HH:mm:ssZ",
            rfc1123: "ddd, dd MMM yyyy HH:mm:ss GMT",
            monthDay: "MMMM dd",
            yearMonth: "MMMM, yyyy"
        },
        regexPatterns: {
            jan: /^jan(uary)?/i,
            feb: /^feb(ruary)?/i,
            mar: /^mar(ch)?/i,
            apr: /^apr(il)?/i,
            may: /^may/i,
            jun: /^jun(e)?/i,
            jul: /^jul(y)?/i,
            aug: /^aug(ust)?/i,
            sep: /^sep(t(ember)?)?/i,
            oct: /^oct(ober)?/i,
            nov: /^nov(ember)?/i,
            dec: /^dec(ember)?/i,
            sun: /^su(n(day)?)?/i,
            mon: /^mo(n(day)?)?/i,
            tue: /^tu(e(s(day)?)?)?/i,
            wed: /^we(d(nesday)?)?/i,
            thu: /^th(u(r(s(day)?)?)?)?/i,
            fri: /^fr(i(day)?)?/i,
            sat: /^sa(t(urday)?)?/i,
            future: /^next/i,
            past: /^last|past|prev(ious)?/i,
            add: /^(\+|aft(er)?|from|hence)/i,
            subtract: /^(\-|bef(ore)?|ago)/i,
            yesterday: /^yes(terday)?/i,
            today: /^t(od(ay)?)?/i,
            tomorrow: /^tom(orrow)?/i,
            now: /^n(ow)?/i,
            millisecond: /^ms|milli(second)?s?/i,
            second: /^sec(ond)?s?/i,
            minute: /^mn|min(ute)?s?/i,
            hour: /^h(our)?s?/i,
            week: /^w(eek)?s?/i,
            month: /^m(onth)?s?/i,
            day: /^d(ay)?s?/i,
            year: /^y(ear)?s?/i,
            shortMeridian: /^(a|p)/i,
            longMeridian: /^(a\.?m?\.?|p\.?m?\.?)/i,
            timezone: /^((e(s|d)t|c(s|d)t|m(s|d)t|p(s|d)t)|((gmt)?\s*(\+|\-)\s*\d\d\d\d?)|gmt|utc)/i,
            ordinalSuffix: /^\s*(st|nd|rd|th)/i,
            timeContext: /^\s*(\:|a(?!u|p)|p)/i
        },
        timezones: [{
            name: "UTC",
            offset: "-000"
        }, {
            name: "GMT",
            offset: "-000"
        }, {
            name: "EST",
            offset: "-0500"
        }, {
            name: "EDT",
            offset: "-0400"
        }, {
            name: "CST",
            offset: "-0600"
        }, {
            name: "CDT",
            offset: "-0500"
        }, {
            name: "MST",
            offset: "-0700"
        }, {
            name: "MDT",
            offset: "-0600"
        }, {
            name: "PST",
            offset: "-0800"
        }, {
            name: "PDT",
            offset: "-0700"
        }]
    },
    function() {
        var e = Date,
            t = e.prototype,
            i = e.CultureInfo,
            n = function(e, t) {
                return t || (t = 2), ("000" + e).slice(-1 * t)
            };
        t.clearTime = function() {
            return this.setHours(0), this.setMinutes(0), this.setSeconds(0), this.setMilliseconds(0), this
        }, t.setTimeToNow = function() {
            var e = new Date;
            return this.setHours(e.getHours()), this.setMinutes(e.getMinutes()), this.setSeconds(e.getSeconds()), this.setMilliseconds(e.getMilliseconds()), this
        }, e.today = function() {
            return (new Date).clearTime()
        }, e.compare = function(e, t) {
            if (isNaN(e) || isNaN(t)) throw new Error(e + " - " + t);
            if (e instanceof Date && t instanceof Date) return t > e ? -1 : e > t ? 1 : 0;
            throw new TypeError(e + " - " + t)
        }, e.equals = function(e, t) {
            return 0 === e.compareTo(t)
        }, e.getDayNumberFromName = function(e) {
            for (var t = i.dayNames, n = i.abbreviatedDayNames, r = i.shortestDayNames, s = e.toLowerCase(), a = 0; a < t.length; a++)
                if (t[a].toLowerCase() == s || n[a].toLowerCase() == s || r[a].toLowerCase() == s) return a;
            return -1
        }, e.getMonthNumberFromName = function(e) {
            for (var t = i.monthNames, n = i.abbreviatedMonthNames, r = e.toLowerCase(), s = 0; s < t.length; s++)
                if (t[s].toLowerCase() == r || n[s].toLowerCase() == r) return s;
            return -1
        }, e.isLeapYear = function(e) {
            return 0 === e % 4 && 0 !== e % 100 || 0 === e % 400
        }, e.getDaysInMonth = function(t, i) {
            return [31, e.isLeapYear(t) ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31][i]
        }, e.getTimezoneAbbreviation = function(e) {
            for (var t = i.timezones, n = 0; n < t.length; n++)
                if (t[n].offset === e) return t[n].name;
            return null
        }, e.getTimezoneOffset = function(e) {
            for (var t = i.timezones, n = 0; n < t.length; n++)
                if (t[n].name === e.toUpperCase()) return t[n].offset;
            return null
        }, t.clone = function() {
            return new Date(this.getTime())
        }, t.compareTo = function(e) {
            return Date.compare(this, e)
        }, t.equals = function(e) {
            return Date.equals(this, e || new Date)
        }, t.between = function(e, t) {
            return this.getTime() >= e.getTime() && this.getTime() <= t.getTime()
        }, t.isAfter = function(e) {
            return 1 === this.compareTo(e || new Date)
        }, t.isBefore = function(e) {
            return -1 === this.compareTo(e || new Date)
        }, t.isToday = function() {
            return this.isSameDay(new Date)
        }, t.isSameDay = function(e) {
            return this.clone().clearTime().equals(e.clone().clearTime())
        }, t.addMilliseconds = function(e) {
            return this.setMilliseconds(this.getMilliseconds() + e), this
        }, t.addSeconds = function(e) {
            return this.addMilliseconds(1e3 * e)
        }, t.addMinutes = function(e) {
            return this.addMilliseconds(6e4 * e)
        }, t.addHours = function(e) {
            return this.addMilliseconds(36e5 * e)
        }, t.addDays = function(e) {
            return this.setDate(this.getDate() + e), this
        }, t.addWeeks = function(e) {
            return this.addDays(7 * e)
        }, t.addMonths = function(t) {
            var i = this.getDate();
            return this.setDate(1), this.setMonth(this.getMonth() + t), this.setDate(Math.min(i, e.getDaysInMonth(this.getFullYear(), this.getMonth()))), this
        }, t.addYears = function(e) {
            return this.addMonths(12 * e)
        }, t.add = function(e) {
            if ("number" == typeof e) return this._orient = e, this;
            var t = e;
            return t.milliseconds && this.addMilliseconds(t.milliseconds), t.seconds && this.addSeconds(t.seconds), t.minutes && this.addMinutes(t.minutes), t.hours && this.addHours(t.hours), t.weeks && this.addWeeks(t.weeks), t.months && this.addMonths(t.months), t.years && this.addYears(t.years), t.days && this.addDays(t.days), this
        };
        var r, s, a;
        t.getWeek = function() {
            var e, t, i, n, o, l, c, d, u, h;
            return r = r ? r : this.getFullYear(), s = s ? s : this.getMonth() + 1, a = a ? a : this.getDate(), 2 >= s ? (e = r - 1, t = (0 | e / 4) - (0 | e / 100) + (0 | e / 400), i = (0 | (e - 1) / 4) - (0 | (e - 1) / 100) + (0 | (e - 1) / 400), u = t - i, o = 0, l = a - 1 + 31 * (s - 1)) : (e = r, t = (0 | e / 4) - (0 | e / 100) + (0 | e / 400), i = (0 | (e - 1) / 4) - (0 | (e - 1) / 100) + (0 | (e - 1) / 400), u = t - i, o = u + 1, l = a + (153 * (s - 3) + 2) / 5 + 58 + u), c = (e + t) % 7, n = (l + c - o) % 7, d = 0 | l + 3 - n, h = 0 > d ? 53 - (0 | (c - u) / 5) : d > 364 + u ? 1 : (0 | d / 7) + 1, r = s = a = null, h
        }, t.getISOWeek = function() {
            return r = this.getUTCFullYear(), s = this.getUTCMonth() + 1, a = this.getUTCDate(), n(this.getWeek())
        }, t.setWeek = function(e) {
            return this.moveToDayOfWeek(1).addWeeks(e - this.getWeek())
        }, e._validate = function(e, t, i, n) {
            if ("undefined" == typeof e) return !1;
            if ("number" != typeof e) throw new TypeError(e + " is not a Number.");
            if (t > e || e > i) throw new RangeError(e + " is not a valid value for " + n + ".");
            return !0
        }, e.validateMillisecond = function(t) {
            return e._validate(t, 0, 999, "millisecond")
        }, e.validateSecond = function(t) {
            return e._validate(t, 0, 59, "second")
        }, e.validateMinute = function(t) {
            return e._validate(t, 0, 59, "minute")
        }, e.validateHour = function(t) {
            return e._validate(t, 0, 23, "hour")
        }, e.validateDay = function(t, i, n) {
            return e._validate(t, 1, e.getDaysInMonth(i, n), "day")
        }, e.validateMonth = function(t) {
            return e._validate(t, 0, 11, "month")
        }, e.validateYear = function(t) {
            return e._validate(t, 0, 9999, "year")
        }, t.set = function(t) {
            return e.validateMillisecond(t.millisecond) && this.addMilliseconds(t.millisecond - this.getMilliseconds()), e.validateSecond(t.second) && this.addSeconds(t.second - this.getSeconds()), e.validateMinute(t.minute) && this.addMinutes(t.minute - this.getMinutes()), e.validateHour(t.hour) && this.addHours(t.hour - this.getHours()), e.validateMonth(t.month) && this.addMonths(t.month - this.getMonth()), e.validateYear(t.year) && this.addYears(t.year - this.getFullYear()), e.validateDay(t.day, this.getFullYear(), this.getMonth()) && this.addDays(t.day - this.getDate()), t.timezone && this.setTimezone(t.timezone), t.timezoneOffset && this.setTimezoneOffset(t.timezoneOffset), t.week && e._validate(t.week, 0, 53, "week") && this.setWeek(t.week), this
        }, t.moveToFirstDayOfMonth = function() {
            return this.set({
                day: 1
            })
        }, t.moveToLastDayOfMonth = function() {
            return this.set({
                day: e.getDaysInMonth(this.getFullYear(), this.getMonth())
            })
        }, t.moveToNthOccurrence = function(e, t) {
            var i = 0;
            if (t > 0) i = t - 1;
            else if (-1 === t) return this.moveToLastDayOfMonth(), this.getDay() !== e && this.moveToDayOfWeek(e, -1), this;
            return this.moveToFirstDayOfMonth().addDays(-1).moveToDayOfWeek(e, 1).addWeeks(i)
        }, t.moveToDayOfWeek = function(e, t) {
            var i = (e - this.getDay() + 7 * (t || 1)) % 7;
            return this.addDays(0 === i ? i += 7 * (t || 1) : i)
        }, t.moveToMonth = function(e, t) {
            var i = (e - this.getMonth() + 12 * (t || 1)) % 12;
            return this.addMonths(0 === i ? i += 12 * (t || 1) : i)
        }, t.getOrdinalNumber = function() {
            return Math.ceil((this.clone().clearTime() - new Date(this.getFullYear(), 0, 1)) / 864e5) + 1
        }, t.getTimezone = function() {
            return e.getTimezoneAbbreviation(this.getUTCOffset())
        }, t.setTimezoneOffset = function(e) {
            var t = this.getTimezoneOffset(),
                i = -6 * Number(e) / 10;
            return this.addMinutes(i - t)
        }, t.setTimezone = function(t) {
            return this.setTimezoneOffset(e.getTimezoneOffset(t))
        }, t.hasDaylightSavingTime = function() {
            return Date.today().set({
                month: 0,
                day: 1
            }).getTimezoneOffset() !== Date.today().set({
                month: 6,
                day: 1
            }).getTimezoneOffset()
        }, t.isDaylightSavingTime = function() {
            return this.hasDaylightSavingTime() && (new Date).getTimezoneOffset() === Date.today().set({
                month: 6,
                day: 1
            }).getTimezoneOffset()
        }, t.getUTCOffset = function() {
            var e, t = -10 * this.getTimezoneOffset() / 6;
            return 0 > t ? (e = (t - 1e4).toString(), e.charAt(0) + e.substr(2)) : (e = (t + 1e4).toString(), "+" + e.substr(1))
        }, t.getElapsed = function(e) {
            return (e || new Date) - this
        }, t.toISOString || (t.toISOString = function() {
            function e(e) {
                return 10 > e ? "0" + e : e
            }
            return '"' + this.getUTCFullYear() + "-" + e(this.getUTCMonth() + 1) + "-" + e(this.getUTCDate()) + "T" + e(this.getUTCHours()) + ":" + e(this.getUTCMinutes()) + ":" + e(this.getUTCSeconds()) + 'Z"'
        }), void 0 == t._toString && (t._toString = t.toString), t.toString = function(e) {
            var t = this;
            if (e && 1 == e.length) {
                var r = i.formatPatterns;
                switch (t.t = t.toString, e) {
                    case "d":
                        return t.t(r.shortDate);
                    case "D":
                        return t.t(r.longDate);
                    case "F":
                        return t.t(r.fullDateTime);
                    case "m":
                        return t.t(r.monthDay);
                    case "r":
                        return t.t(r.rfc1123);
                    case "s":
                        return t.t(r.sortableDateTime);
                    case "t":
                        return t.t(r.shortTime);
                    case "T":
                        return t.t(r.longTime);
                    case "u":
                        return t.t(r.universalSortableDateTime);
                    case "y":
                        return t.t(r.yearMonth)
                }
            }
            var s = function(e) {
                switch (1 * e) {
                    case 1:
                    case 21:
                    case 31:
                        return "st";
                    case 2:
                    case 22:
                        return "nd";
                    case 3:
                    case 23:
                        return "rd";
                    default:
                        return "th"
                }
            };
            return e ? e.replace(/(\\)?(dd?d?d?|MM?M?M?|yy?y?y?|hh?|HH?|mm?|ss?|tt?|S)/g, function(e) {
                if ("\\" === e.charAt(0)) return e.replace("\\", "");
                switch (t.h = t.getHours, e) {
                    case "hh":
                        return n(t.h() < 13 ? 0 === t.h() ? 12 : t.h() : t.h() - 12);
                    case "h":
                        return t.h() < 13 ? 0 === t.h() ? 12 : t.h() : t.h() - 12;
                    case "HH":
                        return n(t.h());
                    case "H":
                        return t.h();
                    case "mm":
                        return n(t.getMinutes());
                    case "m":
                        return t.getMinutes();
                    case "ss":
                        return n(t.getSeconds());
                    case "s":
                        return t.getSeconds();
                    case "yyyy":
                        return n(t.getFullYear(), 4);
                    case "yy":
                        return n(t.getFullYear());
                    case "dddd":
                        return i.dayNames[t.getDay()];
                    case "ddd":
                        return i.abbreviatedDayNames[t.getDay()];
                    case "dd":
                        return n(t.getDate());
                    case "d":
                        return t.getDate();
                    case "MMMM":
                        return i.monthNames[t.getMonth()];
                    case "MMM":
                        return i.abbreviatedMonthNames[t.getMonth()];
                    case "MM":
                        return n(t.getMonth() + 1);
                    case "M":
                        return t.getMonth() + 1;
                    case "t":
                        return t.h() < 12 ? i.amDesignator.substring(0, 1) : i.pmDesignator.substring(0, 1);
                    case "tt":
                        return t.h() < 12 ? i.amDesignator : i.pmDesignator;
                    case "S":
                        return s(t.getDate());
                    default:
                        return e
                }
            }) : this._toString()
        }
    }(),
    function() {
        var e = Date,
            t = e.prototype,
            i = e.CultureInfo,
            n = Number.prototype;
        t._orient = 1, t._nth = null, t._is = !1, t._same = !1, t._isSecond = !1, n._dateElement = "day", t.next = function() {
            return this._orient = 1, this
        }, e.next = function() {
            return e.today().next()
        }, t.last = t.prev = t.previous = function() {
            return this._orient = -1, this
        }, e.last = e.prev = e.previous = function() {
            return e.today().last()
        }, t.is = function() {
            return this._is = !0, this
        }, t.same = function() {
            return this._same = !0, this._isSecond = !1, this
        }, t.today = function() {
            return this.same().day()
        }, t.weekday = function() {
            return this._is ? (this._is = !1, !this.is().sat() && !this.is().sun()) : !1
        }, t.at = function(t) {
            return "string" == typeof t ? e.parse(this.toString("d") + " " + t) : this.set(t)
        }, n.fromNow = n.after = function(e) {
            var t = {};
            return t[this._dateElement] = this, (e ? e.clone() : new Date).add(t)
        }, n.ago = n.before = function(e) {
            var t = {};
            return t[this._dateElement] = -1 * this, (e ? e.clone() : new Date).add(t)
        };
        var r, s = "sunday monday tuesday wednesday thursday friday saturday".split(/\s/),
            a = "january february march april may june july august september october november december".split(/\s/),
            o = "Millisecond Second Minute Hour Day Week Month Year".split(/\s/),
            l = "Milliseconds Seconds Minutes Hours Date Week Month FullYear".split(/\s/),
            c = "final first second third fourth fifth".split(/\s/);
        t.toObject = function() {
            for (var e = {}, t = 0; t < o.length; t++) e[o[t].toLowerCase()] = this["get" + l[t]]();
            return e
        }, e.fromObject = function(e) {
            return e.week = null, Date.today().set(e)
        };
        for (var d = function(t) {
                return function() {
                    if (this._is) return this._is = !1, this.getDay() == t;
                    if (null !== this._nth) {
                        this._isSecond && this.addSeconds(-1 * this._orient), this._isSecond = !1;
                        var i = this._nth;
                        this._nth = null;
                        var n = this.clone().moveToLastDayOfMonth();
                        if (this.moveToNthOccurrence(t, i), this > n) throw new RangeError(e.getDayName(t) + " does not occur " + i + " times in the month of " + e.getMonthName(n.getMonth()) + " " + n.getFullYear() + ".");
                        return this
                    }
                    return this.moveToDayOfWeek(t, this._orient)
                }
            }, u = function(t) {
                return function() {
                    var n = e.today(),
                        r = t - n.getDay();
                    return 0 === t && 1 === i.firstDayOfWeek && 0 !== n.getDay() && (r += 7), n.addDays(r)
                }
            }, h = 0; h < s.length; h++) e[s[h].toUpperCase()] = e[s[h].toUpperCase().substring(0, 3)] = h, e[s[h]] = e[s[h].substring(0, 3)] = u(h), t[s[h]] = t[s[h].substring(0, 3)] = d(h);
        for (var p = function(e) {
                return function() {
                    return this._is ? (this._is = !1, this.getMonth() === e) : this.moveToMonth(e, this._orient)
                }
            }, f = function(t) {
                return function() {
                    return e.today().set({
                        month: t,
                        day: 1
                    })
                }
            }, m = 0; m < a.length; m++) e[a[m].toUpperCase()] = e[a[m].toUpperCase().substring(0, 3)] = m, e[a[m]] = e[a[m].substring(0, 3)] = f(m), t[a[m]] = t[a[m].substring(0, 3)] = p(m);
        for (var g = function(e) {
                return function() {
                    if (this._isSecond) return this._isSecond = !1, this;
                    if (this._same) {
                        this._same = this._is = !1;
                        for (var t = this.toObject(), i = (arguments[0] || new Date).toObject(), n = "", r = e.toLowerCase(), s = o.length - 1; s > -1; s--) {
                            if (n = o[s].toLowerCase(), t[n] != i[n]) return !1;
                            if (r == n) break
                        }
                        return !0
                    }
                    return "s" != e.substring(e.length - 1) && (e += "s"), this["add" + e](this._orient)
                }
            }, _ = function(e) {
                return function() {
                    return this._dateElement = e, this
                }
            }, v = 0; v < o.length; v++) r = o[v].toLowerCase(), t[r] = t[r + "s"] = g(o[v]), n[r] = n[r + "s"] = _(r);
        t._ss = g("Second");
        for (var y = function(e) {
                return function(t) {
                    return this._same ? this._ss(arguments[0]) : t || 0 === t ? this.moveToNthOccurrence(t, e) : (this._nth = e, 2 !== e || void 0 !== t && null !== t ? this : (this._isSecond = !0, this.addSeconds(this._orient)))
                }
            }, b = 0; b < c.length; b++) t[c[b]] = 0 === b ? y(-1) : y(b)
    }(),
    function() {
        Date.Parsing = {
            Exception: function(e) {
                this.message = "Parse error at '" + e.substring(0, 10) + " ...'"
            }
        };
        for (var e = Date.Parsing, t = e.Operators = {
                rtoken: function(t) {
                    return function(i) {
                        var n = i.match(t);
                        if (n) return [n[0], i.substring(n[0].length)];
                        throw new e.Exception(i)
                    }
                },
                token: function() {
                    return function(e) {
                        return t.rtoken(new RegExp("^s*" + e + "s*"))(e)
                    }
                },
                stoken: function(e) {
                    return t.rtoken(new RegExp("^" + e))
                },
                until: function(e) {
                    return function(t) {
                        for (var i = [], n = null; t.length;) {
                            try {
                                n = e.call(this, t)
                            } catch (r) {
                                i.push(n[0]), t = n[1];
                                continue
                            }
                            break
                        }
                        return [i, t]
                    }
                },
                many: function(e) {
                    return function(t) {
                        for (var i = [], n = null; t.length;) {
                            try {
                                n = e.call(this, t)
                            } catch (r) {
                                return [i, t]
                            }
                            i.push(n[0]), t = n[1]
                        }
                        return [i, t]
                    }
                },
                optional: function(e) {
                    return function(t) {
                        var i = null;
                        try {
                            i = e.call(this, t)
                        } catch (n) {
                            return [null, t]
                        }
                        return [i[0], i[1]]
                    }
                },
                not: function(t) {
                    return function(i) {
                        try {
                            t.call(this, i)
                        } catch (n) {
                            return [null, i]
                        }
                        throw new e.Exception(i)
                    }
                },
                ignore: function(e) {
                    return e ? function(t) {
                        var i = null;
                        return i = e.call(this, t), [null, i[1]]
                    } : null
                },
                product: function() {
                    for (var e = arguments[0], i = Array.prototype.slice.call(arguments, 1), n = [], r = 0; r < e.length; r++) n.push(t.each(e[r], i));
                    return n
                },
                cache: function(t) {
                    var i = {},
                        n = null;
                    return function(r) {
                        try {
                            n = i[r] = i[r] || t.call(this, r)
                        } catch (s) {
                            n = i[r] = s
                        }
                        if (n instanceof e.Exception) throw n;
                        return n
                    }
                },
                any: function() {
                    var t = arguments;
                    return function(i) {
                        for (var n = null, r = 0; r < t.length; r++)
                            if (null != t[r]) {
                                try {
                                    n = t[r].call(this, i)
                                } catch (s) {
                                    n = null
                                }
                                if (n) return n
                            }
                        throw new e.Exception(i)
                    }
                },
                each: function() {
                    var t = arguments;
                    return function(i) {
                        for (var n = [], r = null, s = 0; s < t.length; s++)
                            if (null != t[s]) {
                                try {
                                    r = t[s].call(this, i)
                                } catch (a) {
                                    throw new e.Exception(i)
                                }
                                n.push(r[0]), i = r[1]
                            }
                        return [n, i]
                    }
                },
                all: function() {
                    var e = arguments,
                        t = t;
                    return t.each(t.optional(e))
                },
                sequence: function(i, n, r) {
                    return n = n || t.rtoken(/^\s*/), r = r || null, 1 == i.length ? i[0] : function(t) {
                        for (var s = null, a = null, o = [], l = 0; l < i.length; l++) {
                            try {
                                s = i[l].call(this, t)
                            } catch (c) {
                                break
                            }
                            o.push(s[0]);
                            try {
                                a = n.call(this, s[1])
                            } catch (d) {
                                a = null;
                                break
                            }
                            t = a[1]
                        }
                        if (!s) throw new e.Exception(t);
                        if (a) throw new e.Exception(a[1]);
                        if (r) try {
                            s = r.call(this, s[1])
                        } catch (u) {
                            throw new e.Exception(s[1])
                        }
                        return [o, s ? s[1] : t]
                    }
                },
                between: function(e, i, n) {
                    n = n || e;
                    var s = t.each(t.ignore(e), i, t.ignore(n));
                    return function(e) {
                        var t = s.call(this, e);
                        return [
                            [t[0][0], r[0][2]], t[1]
                        ]
                    }
                },
                list: function(e, i, n) {
                    return i = i || t.rtoken(/^\s*/), n = n || null, e instanceof Array ? t.each(t.product(e.slice(0, -1), t.ignore(i)), e.slice(-1), t.ignore(n)) : t.each(t.many(t.each(e, t.ignore(i))), px, t.ignore(n))
                },
                set: function(i, n, r) {
                    return n = n || t.rtoken(/^\s*/), r = r || null,
                        function(s) {
                            for (var a = null, o = null, l = null, c = null, d = [
                                    [], s
                                ], u = !1, h = 0; h < i.length; h++) {
                                l = null, o = null, a = null, u = 1 == i.length;
                                try {
                                    a = i[h].call(this, s)
                                } catch (p) {
                                    continue
                                }
                                if (c = [
                                        [a[0]], a[1]
                                    ], a[1].length > 0 && !u) try {
                                    l = n.call(this, a[1])
                                } catch (f) {
                                    u = !0
                                } else u = !0;
                                if (u || 0 !== l[1].length || (u = !0), !u) {
                                    for (var m = [], g = 0; g < i.length; g++) h != g && m.push(i[g]);
                                    o = t.set(m, n).call(this, l[1]), o[0].length > 0 && (c[0] = c[0].concat(o[0]), c[1] = o[1])
                                }
                                if (c[1].length < d[1].length && (d = c), 0 === d[1].length) break
                            }
                            if (0 === d[0].length) return d;
                            if (r) {
                                try {
                                    l = r.call(this, d[1])
                                } catch (_) {
                                    throw new e.Exception(d[1])
                                }
                                d[1] = l[1]
                            }
                            return d
                        }
                },
                forward: function(e, t) {
                    return function(i) {
                        return e[t].call(this, i)
                    }
                },
                replace: function(e, t) {
                    return function(i) {
                        var n = e.call(this, i);
                        return [t, n[1]]
                    }
                },
                process: function(e, t) {
                    return function(i) {
                        var n = e.call(this, i);
                        return [t.call(this, n[0]), n[1]]
                    }
                },
                min: function(t, i) {
                    return function(n) {
                        var r = i.call(this, n);
                        if (r[0].length < t) throw new e.Exception(n);
                        return r
                    }
                }
            }, i = function(e) {
                return function() {
                    var t = null,
                        i = [];
                    if (arguments.length > 1 ? t = Array.prototype.slice.call(arguments) : arguments[0] instanceof Array && (t = arguments[0]), !t) return e.apply(null, arguments);
                    for (var n = 0, r = t.shift(); n < r.length; n++) return t.unshift(r[n]), i.push(e.apply(null, t)), t.shift(), i
                }
            }, n = "optional not ignore cache".split(/\s/), s = 0; s < n.length; s++) t[n[s]] = i(t[n[s]]);
        for (var a = function(e) {
                return function() {
                    return arguments[0] instanceof Array ? e.apply(null, arguments[0]) : e.apply(null, arguments)
                }
            }, o = "each any all".split(/\s/), l = 0; l < o.length; l++) t[o[l]] = a(t[o[l]])
    }(),
    function() {
        var e = Date,
            t = (e.prototype, e.CultureInfo),
            i = function(e) {
                for (var t = [], n = 0; n < e.length; n++) e[n] instanceof Array ? t = t.concat(i(e[n])) : e[n] && t.push(e[n]);
                return t
            };
        e.Grammar = {}, e.Translator = {
            hour: function(e) {
                return function() {
                    this.hour = Number(e)
                }
            },
            minute: function(e) {
                return function() {
                    this.minute = Number(e)
                }
            },
            second: function(e) {
                return function() {
                    this.second = Number(e)
                }
            },
            meridian: function(e) {
                return function() {
                    this.meridian = e.slice(0, 1).toLowerCase()
                }
            },
            timezone: function(e) {
                return function() {
                    var t = e.replace(/[^\d\+\-]/g, "");
                    t.length ? this.timezoneOffset = Number(t) : this.timezone = e.toLowerCase()
                }
            },
            day: function(e) {
                var t = e[0];
                return function() {
                    this.day = Number(t.match(/\d+/)[0])
                }
            },
            month: function(e) {
                return function() {
                    this.month = 3 == e.length ? "jan feb mar apr may jun jul aug sep oct nov dec".indexOf(e) / 4 : Number(e) - 1
                }
            },
            year: function(e) {
                return function() {
                    var i = Number(e);
                    this.year = e.length > 2 ? i : i + (i + 2e3 < t.twoDigitYearMax ? 2e3 : 1900)
                }
            },
            rday: function(e) {
                return function() {
                    switch (e) {
                        case "yesterday":
                            this.days = -1;
                            break;
                        case "tomorrow":
                            this.days = 1;
                            break;
                        case "today":
                            this.days = 0;
                            break;
                        case "now":
                            this.days = 0, this.now = !0
                    }
                }
            },
            finishExact: function(t) {
                t = t instanceof Array ? t : [t];
                for (var i = 0; i < t.length; i++) t[i] && t[i].call(this);
                var n = new Date;
                if (!this.hour && !this.minute || this.month || this.year || this.day || (this.day = n.getDate()), this.year || (this.year = n.getFullYear()), this.month || 0 === this.month || (this.month = n.getMonth()), this.day || (this.day = 1), this.hour || (this.hour = 0), this.minute || (this.minute = 0), this.second || (this.second = 0), this.meridian && this.hour && ("p" == this.meridian && this.hour < 12 ? this.hour = this.hour + 12 : "a" == this.meridian && 12 == this.hour && (this.hour = 0)), this.day > e.getDaysInMonth(this.year, this.month)) throw new RangeError(this.day + " is not a valid value for days.");
                var r = new Date(this.year, this.month, this.day, this.hour, this.minute, this.second);
                return this.timezone ? r.set({
                    timezone: this.timezone
                }) : this.timezoneOffset && r.set({
                    timezoneOffset: this.timezoneOffset
                }), r
            },
            finish: function(t) {
                if (t = t instanceof Array ? i(t) : [t], 0 === t.length) return null;
                for (var n = 0; n < t.length; n++) "function" == typeof t[n] && t[n].call(this);
                var r = e.today();
                if (this.now && !this.unit && !this.operator) return new Date;
                this.now && (r = new Date);
                var s, a, o, l = !!(this.days && null !== this.days || this.orient || this.operator);
                if (o = "past" == this.orient || "subtract" == this.operator ? -1 : 1, this.now || -1 == "hour minute second".indexOf(this.unit) || r.setTimeToNow(), (this.month || 0 === this.month) && -1 != "year day hour minute second".indexOf(this.unit) && (this.value = this.month + 1, this.month = null, l = !0), !l && this.weekday && !this.day && !this.days) {
                    var c = Date[this.weekday]();
                    this.day = c.getDate(), this.month || (this.month = c.getMonth()), this.year = c.getFullYear()
                }
                if (l && this.weekday && "month" != this.unit && (this.unit = "day", s = e.getDayNumberFromName(this.weekday) - r.getDay(), a = 7, this.days = s ? (s + o * a) % a : o * a), this.month && "day" == this.unit && this.operator && (this.value = this.month + 1, this.month = null), null != this.value && null != this.month && null != this.year && (this.day = 1 * this.value), this.month && !this.day && this.value && (r.set({
                        day: 1 * this.value
                    }), l || (this.day = 1 * this.value)), this.month || !this.value || "month" != this.unit || this.now || (this.month = this.value, l = !0), l && (this.month || 0 === this.month) && "year" != this.unit && (this.unit = "month", s = this.month - r.getMonth(), a = 12, this.months = s ? (s + o * a) % a : o * a, this.month = null), this.unit || (this.unit = "day"), !this.value && this.operator && null !== this.operator && this[this.unit + "s"] && null !== this[this.unit + "s"] ? this[this.unit + "s"] = this[this.unit + "s"] + ("add" == this.operator ? 1 : -1) + (this.value || 0) * o : (null == this[this.unit + "s"] || null != this.operator) && (this.value || (this.value = 1), this[this.unit + "s"] = this.value * o), this.meridian && this.hour && ("p" == this.meridian && this.hour < 12 ? this.hour = this.hour + 12 : "a" == this.meridian && 12 == this.hour && (this.hour = 0)), this.weekday && !this.day && !this.days) {
                    var c = Date[this.weekday]();
                    this.day = c.getDate(), c.getMonth() !== r.getMonth() && (this.month = c.getMonth())
                }
                return !this.month && 0 !== this.month || this.day || (this.day = 1), this.orient || this.operator || "week" != this.unit || !this.value || this.day || this.month ? (l && this.timezone && this.day && this.days && (this.day = this.days), l ? r.add(this) : r.set(this)) : Date.today().setWeek(this.value)
            }
        };
        var n, r = e.Parsing.Operators,
            s = e.Grammar,
            a = e.Translator;
        s.datePartDelimiter = r.rtoken(/^([\s\-\.\,\/\x27]+)/), s.timePartDelimiter = r.stoken(":"), s.whiteSpace = r.rtoken(/^\s*/), s.generalDelimiter = r.rtoken(/^(([\s\,]|at|@|on)+)/);
        var o = {};
        s.ctoken = function(e) {
            var i = o[e];
            if (!i) {
                for (var n = t.regexPatterns, s = e.split(/\s+/), a = [], l = 0; l < s.length; l++) a.push(r.replace(r.rtoken(n[s[l]]), s[l]));
                i = o[e] = r.any.apply(null, a)
            }
            return i
        }, s.ctoken2 = function(e) {
            return r.rtoken(t.regexPatterns[e])
        }, s.h = r.cache(r.process(r.rtoken(/^(0[0-9]|1[0-2]|[1-9])/), a.hour)), s.hh = r.cache(r.process(r.rtoken(/^(0[0-9]|1[0-2])/), a.hour)), s.H = r.cache(r.process(r.rtoken(/^([0-1][0-9]|2[0-3]|[0-9])/), a.hour)), s.HH = r.cache(r.process(r.rtoken(/^([0-1][0-9]|2[0-3])/), a.hour)), s.m = r.cache(r.process(r.rtoken(/^([0-5][0-9]|[0-9])/), a.minute)), s.mm = r.cache(r.process(r.rtoken(/^[0-5][0-9]/), a.minute)), s.s = r.cache(r.process(r.rtoken(/^([0-5][0-9]|[0-9])/), a.second)), s.ss = r.cache(r.process(r.rtoken(/^[0-5][0-9]/), a.second)), s.hms = r.cache(r.sequence([s.H, s.m, s.s], s.timePartDelimiter)), s.t = r.cache(r.process(s.ctoken2("shortMeridian"), a.meridian)), s.tt = r.cache(r.process(s.ctoken2("longMeridian"), a.meridian)), s.z = r.cache(r.process(r.rtoken(/^((\+|\-)\s*\d\d\d\d)|((\+|\-)\d\d\:?\d\d)/), a.timezone)), s.zz = r.cache(r.process(r.rtoken(/^((\+|\-)\s*\d\d\d\d)|((\+|\-)\d\d\:?\d\d)/), a.timezone)), s.zzz = r.cache(r.process(s.ctoken2("timezone"), a.timezone)), s.timeSuffix = r.each(r.ignore(s.whiteSpace), r.set([s.tt, s.zzz])), s.time = r.each(r.optional(r.ignore(r.stoken("T"))), s.hms, s.timeSuffix), s.d = r.cache(r.process(r.each(r.rtoken(/^([0-2]\d|3[0-1]|\d)/), r.optional(s.ctoken2("ordinalSuffix"))), a.day)), s.dd = r.cache(r.process(r.each(r.rtoken(/^([0-2]\d|3[0-1])/), r.optional(s.ctoken2("ordinalSuffix"))), a.day)), s.ddd = s.dddd = r.cache(r.process(s.ctoken("sun mon tue wed thu fri sat"), function(e) {
            return function() {
                this.weekday = e
            }
        })), s.M = r.cache(r.process(r.rtoken(/^(1[0-2]|0\d|\d)/), a.month)), s.MM = r.cache(r.process(r.rtoken(/^(1[0-2]|0\d)/), a.month)), s.MMM = s.MMMM = r.cache(r.process(s.ctoken("jan feb mar apr may jun jul aug sep oct nov dec"), a.month)), s.y = r.cache(r.process(r.rtoken(/^(\d\d?)/), a.year)), s.yy = r.cache(r.process(r.rtoken(/^(\d\d)/), a.year)), s.yyy = r.cache(r.process(r.rtoken(/^(\d\d?\d?\d?)/), a.year)), s.yyyy = r.cache(r.process(r.rtoken(/^(\d\d\d\d)/), a.year)), n = function() {
            return r.each(r.any.apply(null, arguments), r.not(s.ctoken2("timeContext")))
        }, s.day = n(s.d, s.dd), s.month = n(s.M, s.MMM), s.year = n(s.yyyy, s.yy), s.orientation = r.process(s.ctoken("past future"), function(e) {
            return function() {
                this.orient = e
            }
        }), s.operator = r.process(s.ctoken("add subtract"), function(e) {
            return function() {
                this.operator = e
            }
        }), s.rday = r.process(s.ctoken("yesterday tomorrow today now"), a.rday), s.unit = r.process(s.ctoken("second minute hour day week month year"), function(e) {
            return function() {
                this.unit = e
            }
        }), s.value = r.process(r.rtoken(/^\d\d?(st|nd|rd|th)?/), function(e) {
            return function() {
                this.value = e.replace(/\D/g, "")
            }
        }), s.expression = r.set([s.rday, s.operator, s.value, s.unit, s.orientation, s.ddd, s.MMM]), n = function() {
            return r.set(arguments, s.datePartDelimiter)
        }, s.mdy = n(s.ddd, s.month, s.day, s.year), s.ymd = n(s.ddd, s.year, s.month, s.day), s.dmy = n(s.ddd, s.day, s.month, s.year), s.date = function(e) {
            return (s[t.dateElementOrder] || s.mdy).call(this, e)
        }, s.format = r.process(r.many(r.any(r.process(r.rtoken(/^(dd?d?d?|MM?M?M?|yy?y?y?|hh?|HH?|mm?|ss?|tt?|zz?z?)/), function(t) {
            if (s[t]) return s[t];
            throw e.Parsing.Exception(t)
        }), r.process(r.rtoken(/^[^dMyhHmstz]+/), function(e) {
            return r.ignore(r.stoken(e))
        }))), function(e) {
            return r.process(r.each.apply(null, e), a.finishExact)
        });
        var l = {},
            c = function(e) {
                return l[e] = l[e] || s.format(e)[0]
            };
        s.formats = function(e) {
            if (e instanceof Array) {
                for (var t = [], i = 0; i < e.length; i++) t.push(c(e[i]));
                return r.any.apply(null, t)
            }
            return c(e)
        }, s._formats = s.formats(['"yyyy-MM-ddTHH:mm:ssZ"', "yyyy-MM-ddTHH:mm:ssZ", "yyyy-MM-ddTHH:mm:ssz", "yyyy-MM-ddTHH:mm:ss", "yyyy-MM-ddTHH:mmZ", "yyyy-MM-ddTHH:mmz", "yyyy-MM-ddTHH:mm", "ddd, MMM dd, yyyy H:mm:ss tt", "ddd MMM d yyyy HH:mm:ss zzz", "MMddyyyy", "ddMMyyyy", "Mddyyyy", "ddMyyyy", "Mdyyyy", "dMyyyy", "yyyy", "Mdyy", "dMyy", "d"]), s._start = r.process(r.set([s.date, s.time, s.expression], s.generalDelimiter, s.whiteSpace), a.finish), s.start = function(e) {
            try {
                var t = s._formats.call({}, e);
                if (0 === t[1].length) return t
            } catch (i) {}
            return s._start.call({}, e)
        }, e._parse = e.parse, e.parse = function(t) {
            var i = null;
            if (!t) return null;
            if (t instanceof Date) return t;
            try {
                i = e.Grammar.start.call({}, t.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1"))
            } catch (n) {
                return null
            }
            return 0 === i[1].length ? i[0] : null
        }, e.getParseFunction = function(t) {
            var i = e.Grammar.formats(t);
            return function(e) {
                var t = null;
                try {
                    t = i.call({}, e)
                } catch (n) {
                    return null
                }
                return 0 === t[1].length ? t[0] : null
            }
        }, e.parseExact = function(t, i) {
            return e.getParseFunction(i)(t)
        }
    }(), jQuery("[rel=remote-contact-hover]").livequery(function() {
        jQuery(this).popover({
            delayOut: 300,
            trigger: "manual",
            offset: 5,
            html: !0,
            reloadContent: !1,
            template: '<div class="dbl_left arrow"></div><div class="hover_card inner"><div class="content"><p></p></div></div>',
            content: function() {
                var e = "user-info-div-" + $(this).data("contactId");
                return jQuery("#" + e).html() || "<div class='sloading loading-small loading-block' id='" + e + "' rel='remote-load' data-url='" + $(this).data("contactUrl") + "'></div>"
            }
        })
    }), window.App = window.App || {},
    function(e) {
        "use strict";
        App.Metrics = {
            allowOnly: ["admin/portal/index", "discussions/index", "discussions/your_topics"],
            allowLike: [],
            allowed: function() {
                var e;
                if (this.allowOnly.indexOf(App.namespace) > -1) return !0;
                for (e = 0; e < this.allowLike.length; e += 1)
                    if (this.allowLike[e].test(App.namespace)) return !0
            }
        }, App = e.extend(!0, App, {
            previous_namespace: null,
            track: function(e, t) {
                return "undefined" == typeof mixpanel ? !1 : (t = t || {}, mixpanel.track(e, t), !0)
            },
            trackPageView: function() {
                return "undefined" == typeof mixpanel ? !1 : App.Metrics.allowed() ? (mixpanel.track(App.namespace, {
                    referrer: App.previous_namespace
                }), !0) : !1
            },
            startMetrics: function() {
                App.trackPageView(), e(document).on("pjax:beforeSend", function() {
                    App.previous_namespace = App.namespace
                }), e(document).on("pjax:end", function() {
                    App.trackPageView()
                }), e("body").on("click.app_metrics", "[data-track]", function() {
                    var t = e(this);
                    App.track(t.data("track"), t.data("eventData"))
                })
            }
        })
    }(window.jQuery), window.jQuery(function() {
        "use strict";
        "undefined" != typeof mixpanel && App.startMetrics()
    }), window.App = window.App || {}, window.App.Header = window.App.Header || {},
    function(e) {
        "use strict";
        App.Header = {
            assumable_loaded: 0,
            init: function(e) {
                this.assumed_identity = e, this.bindEvent()
            },
            bindEvent: function() {
                e("#header-profile-avatar").on("click", this.loadAssumableAgents.bind(this)), e("#toggle_shortcut").on("change", this.toggleShortcuts.bind(this)), e("#shortcuts_info").on("click", this.loadShortcutInfo.bind(this)), e("#available_icon").on("click", this.toggleAvailability.bind(this)), e("#assumed_select_id").on("change", this.assumeIdentityUrl.bind(this))
            },
            loadAssumableAgents: function() {
                if (!this.assumable_loaded && !this.assumed_identity) {
                    var t = this,
                        i = jQuery("#assumed_select_id");
                    jQuery.ajax({
                        url: "/users/assumable_agents",
                        type: "GET",
                        success: function(n) {
                            0 == n.length ? jQuery("#switch_agent_container").remove() : (e.each(n, function(e, t) {
                                i.append("<option id='" + t.id + "' value='" + t.id + "'>" + t.value + "</option>")
                            }), i.select2().show()), jQuery("#agt_loading").remove(), t.assumable_loaded = 1
                        }
                    })
                }
            },
            toggleShortcuts: function(t) {
                var i = t.currentTarget;
                i.disabled = !0, jQuery.ajax({
                    type: "PUT",
                    dataType: "json",
                    url: e(i).data("remoteUrl"),
                    success: function(e) {
                        i.disabled = !1, e.shortcuts_enabled ? void 0 !== window.shortcuts ? jQuery(document).trigger("shortcuts:invoke") : Fjax.Assets.plugin("shortcut") : jQuery(document).trigger("shortcuts:destroy")
                    }
                })
            },
            loadShortcutInfo: function(e) {
                e.preventDefault(), jQuery("#shortcut_help_chart").trigger("click")
            },
            toggleAvailability: function(t) {
                var i = {
                    value: !("header-icons-agent-roundrobin-on" == jQuery("#available_icon").attr("class")),
                    id: DataStore.get("current_user").currentData.user.id
                };
                jQuery.ajax({
                    type: "POST",
                    url: e(t.currentTarget).data("remoteUrl"),
                    data: i,
                    beforeSend: function() {
                        jQuery("#available_icon").addClass("header-spinner")
                    },
                    success: function() {
                        var e = jQuery("#availabilty-toggle");
                        jQuery("#available_icon").removeClass("header-spinner"), jQuery("#available_icon").hasClass("header-icons-agent-roundrobin-on") ? (jQuery("#available_icon").removeClass("header-icons-agent-roundrobin-on").addClass("header-icons-agent-roundrobin-off"), e.attr("title", e.data("assignOn"))) : (jQuery("#available_icon").removeClass("header-icons-agent-roundrobin-off").addClass("header-icons-agent-roundrobin-on"), e.attr("title", e.data("assignOff")))
                    }
                })
            },
            assumeIdentityUrl: function() {
                window.location = "/users/" + e("#assumed_select_id").val() + "/assume_identity"
            }
        }
    }(window.jQuery), window.App = window.App || {},
    function(e) {}(window.jQuery), window.App = window.App || {},
    function() {
        "use strict";
        App.Merge = {
            initialize: function() {
                this.bindHandlers()
            },
            bindHandlers: function() {
                this.searchMergeKeyup(), this.cancelClick()
            },
            clearSearchField: function(e) {
                jQuery(".search_merge").val(""), e.removeClass("typed")
            },
            appendToMergeList: function(e, t) {
                e.removeClass("cont-primary present-contact"), e.find(".merge_element").replaceWith(t.children(".merge_element").clone()), e.appendTo(jQuery(".merge_entity")), jQuery(t).hasClass("contactdiv") || this.makePrimary(App.Tickets.Merge_tickets.findOldestTicket()), t.children("#resp-icon").addClass("clicked")
            },
            makePrimary: function(e) {
                jQuery(".merge-cont").removeClass("cont-primary"), jQuery(".merge-cont").children(".primary-marker").attr("title", "Mark as primary"), e.addClass("cont-primary"), e.children(".primary-marker").attr("title", "Primary ticket"), jQuery("#merge-warning").toggleClass("hide", this.ticketId(e) === this.ticketId(App.Tickets.Merge_tickets.findOldestTicket()))
            },
            ticketId: function(e) {
                return e.find("#merge-ticket").data("id")
            },
            createdDate: function() {
                return element.find(".merge_element").data("created")
            },
            searchMergeKeyup: function() {
                jQuery("body").on("keyup.merge_helpdesk", ".search_merge", function() {
                    jQuery(this).closest(".searchicon").toggleClass("typed", "" != jQuery(this).val())
                })
            },
            cancelClick: function() {
                jQuery("body").on("click.merge_helpdesk", "#cancel_new_merge, #cancel-user-merge", function() {
                    active_dialog && active_dialog.dialog("close"), jQuery("#mergebox1").modal("hide"), jQuery("#merge_freshdialog").modal("hide"), jQuery("#merge_freshdialog-content").html('<span class="loading-block sloading loading-small">')
                })
            }
        }
    }(window.jQuery),
    function(e) {
        "use strict";
        var t = function(e) {
            this.initialize(e)
        };
        t.prototype = {
            constructor: t,
            contentChanged: !1,
            savingContentFlag: !1,
            successCount: 0,
            failureCount: 0,
            totalCount: 0,
            lastSaveStatus: !0,
            timer: null,
            minContentLengthCheck: !0,
            opts: {
                autosaveInterval: 3e4,
                autosaveUrl: window.location.pathname,
                monitorChangesOf: {
                    description: "#solution_article_description",
                    title: "#solution_article_title"
                },
                extraParams: {},
                minContentLength: 0,
                retryIfError: !1,
                responseCallback: function() {}
            },
            initialize: function(t) {
                this.opts = e.extend(this.opts, t), this.startSaving()
            },
            bindEvents: function() {
                var t = this;
                e.each(this.opts.monitorChangesOf, function(i, n) {
                    var r = e(n);
                    r.data("previousSavedData", r.val()), e(n).on("change.autosave redactor:sync.autosave froalaEditor.contentChanged keyup.autosave", function() {
                        r.data("previousSavedData") !== r.val() && (t.contentChanged = !0)
                    })
                })
            },
            getContent: function() {
                this.content = {}, this.getMainContent(), this.getExtraParams()
            },
            getMainContent: function() {
                var t = this;
                e.each(this.opts.monitorChangesOf, function(i, n) {
                    var r = e(n);
                    t.content[i] = r.val(), "minContentLength" in t.opts && null != t.content[i] && (t.minContentLengthCheck = t.content[i].length > t.opts.minContentLength, 0 == t.minContentLengthCheck) || r.data("previousSavedData", r.val())
                })
            },
            getExtraParams: function() {
                var t = this;
                e.isEmptyObject(this.opts.extraParams) || e.each(this.opts.extraParams, function(e, i) {
                    t.content[e] = i
                })
            },
            autoSaveTrigger: function() {
                var e = this;
                this.timer = setInterval(function() {
                    e.contentChanged && (e.lastSaveStatus || !e.lastSaveStatus && e.opts.retryIfError) && e.saveContent()
                }, this.opts.autosaveInterval)
            },
            saveContent: function() {
                this.getContent(), this.minContentLengthCheck && !this.savingContentFlag && (this.savingContentFlag = !0, this.totalCount += 1, e.ajax({
                    url: this.opts.autosaveUrl,
                    type: "POST",
                    data: this.content,
                    success: e.proxy(this.onSaveSuccess, this),
                    error: e.proxy(this.onSaveError, this)
                }))
            },
            onSaveSuccess: function(e) {
                this.contentChanged = !1, this.updateExtraParams(e), this.savingContentFlag = !1, this.lastSaveStatus = !0, this.successCount += 1, this.opts.responseCallback(e)
            },
            onSaveError: function(e) {
                this.savingContentFlag = !1, this.contentChanged = !0, this.lastSaveStatus = !1, this.failureCount += 1, this.opts.responseCallback(e.status)
            },
            updateExtraParams: function(t) {
                var i = this;
                e.isEmptyObject(this.opts.extraParams) || e.each(this.opts.extraParams, function(e) {
                    t[e] && (i.opts.extraParams[e] = t[e])
                })
            },
            stopSaving: function() {
                e.each(this.opts.monitorChangesOf, function(t, i) {
                    e(i).off(".autosave")
                }), clearInterval(this.timer)
            },
            startSaving: function() {
                this.bindEvents(), this.autoSaveTrigger()
            }
        }, e.autoSaveContent = function(e) {
            return new t(e)
        }
    }(window.jQuery),
    function(e) {
        "function" == typeof define && define.amd ? define(["jquery"], e) : "object" == typeof module && module.exports ? e(require("jquery")) : e(jQuery)
    }(function(e) {
        function t(t) {
            var i = {},
                n = /^jQuery\d+$/;
            return e.each(t.attributes, function(e, t) {
                t.specified && !n.test(t.name) && (i[t.name] = t.value)
            }), i
        }

        function i(t, i) {
            var n = this,
                s = e(n);
            if (n.value === s.attr("placeholder") && s.hasClass(h.customClass))
                if (n.value = "", s.removeClass(h.customClass), s.data("placeholder-password")) {
                    if (s = s.hide().nextAll('input[type="password"]:first').show().attr("id", s.removeAttr("id").data("placeholder-id")), t === !0) return s[0].value = i, i;
                    s.focus()
                } else n == r() && n.select()
        }

        function n(n) {
            var r, s = this,
                a = e(s),
                o = s.id;
            if (n && "blur" === n.type) {
                if (a.hasClass(h.customClass)) return;
                if ("password" === s.type && (r = a.prevAll('input[type="text"]:first'), r.length > 0 && r.is(":visible"))) return
            }
            if ("" === s.value) {
                if ("password" === s.type) {
                    if (!a.data("placeholder-textinput")) {
                        try {
                            r = a.clone().prop({
                                type: "text"
                            })
                        } catch (l) {
                            r = e("<input>").attr(e.extend(t(this), {
                                type: "text"
                            }))
                        }
                        r.removeAttr("name").data({
                            "placeholder-enabled": !0,
                            "placeholder-password": a,
                            "placeholder-id": o
                        }).bind("focus.placeholder", i), a.data({
                            "placeholder-textinput": r,
                            "placeholder-id": o
                        }).before(r)
                    }
                    s.value = "", a = a.removeAttr("id").hide().prevAll('input[type="text"]:first').attr("id", a.data("placeholder-id")).show()
                } else {
                    var c = a.data("placeholder-password");
                    c && (c[0].value = "", a.attr("id", a.data("placeholder-id")).show().nextAll('input[type="password"]:last').hide().removeAttr("id"))
                }
                a.addClass(h.customClass), a[0].value = a.attr("placeholder")
            } else a.removeClass(h.customClass)
        }

        function r() {
            try {
                return document.activeElement
            } catch (e) {}
        }
        var s, a, o = "[object OperaMini]" === Object.prototype.toString.call(window.operamini),
            l = "placeholder" in document.createElement("input") && !o,
            c = "placeholder" in document.createElement("textarea") && !o,
            d = e.valHooks,
            u = e.propHooks,
            h = {};
        l && c ? (a = e.fn.placeholder = function() {
            return this
        }, a.input = !0, a.textarea = !0) : (a = e.fn.placeholder = function(t) {
            var r = {
                customClass: "placeholder"
            };
            return h = e.extend({}, r, t), this.filter((l ? "textarea" : ":input") + "[placeholder]").not("." + h.customClass).bind({
                "focus.placeholder": i,
                "blur.placeholder": n
            }).data("placeholder-enabled", !0).trigger("blur.placeholder")
        }, a.input = l, a.textarea = c, s = {
            get: function(t) {
                var i = e(t),
                    n = i.data("placeholder-password");
                return n ? n[0].value : i.data("placeholder-enabled") && i.hasClass(h.customClass) ? "" : t.value
            },
            set: function(t, s) {
                var a, o, l = e(t);
                return "" !== s && (a = l.data("placeholder-textinput"), o = l.data("placeholder-password"), a ? (i.call(a[0], !0, s) || (t.value = s), a[0].value = s) : o && (i.call(t, !0, s) || (o[0].value = s), t.value = s)), l.data("placeholder-enabled") ? ("" === s ? (t.value = s, t != r() && n.call(t)) : (l.hasClass(h.customClass) && i.call(t), t.value = s), l) : (t.value = s, l)
            }
        }, l || (d.input = s, u.value = s), c || (d.textarea = s, u.value = s), e(function() {
            e(document).delegate("form", "submit.placeholder", function() {
                var t = e("." + h.customClass, this).each(function() {
                    i.call(this, !0, "")
                });
                setTimeout(function() {
                    t.each(n)
                }, 10)
            })
        }), e(window).bind("beforeunload.placeholder", function() {
            e("." + h.customClass).each(function() {
                this.value = ""
            })
        }))
    }),
    function(e) {
        if ("undefined" != typeof module && module.exports) module.exports = e(this);
        else if ("function" == typeof define && define.amd) {
            var t = this;
            define("i18n", function() {
                return e(t)
            })
        } else this.I18n = e(this)
    }(function(e) {
        "use strict";
        var t = e && e.I18n || {},
            i = Array.prototype.slice,
            n = function(e) {
                return ("0" + e.toString()).substr(-2)
            },
            r = {
                day_names: ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
                abbr_day_names: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
                month_names: [null, "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
                abbr_month_names: [null, "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                meridian: ["AM", "PM"]
            },
            s = {
                precision: 3,
                separator: ".",
                delimiter: ",",
                strip_insignificant_zeros: !1
            },
            a = {
                unit: "$",
                precision: 2,
                format: "%u%n",
                sign_first: !0,
                delimiter: ",",
                separator: "."
            },
            o = {
                unit: "%",
                precision: 3,
                format: "%n%u",
                separator: ".",
                delimiter: ""
            },
            l = [null, "kb", "mb", "gb", "tb"],
            c = {
                defaultLocale: "en",
                locale: "en",
                defaultSeparator: ".",
                placeholder: /(?:\{\{|%\{)(.*?)(?:\}\}?)/gm,
                fallbacks: !1,
                translations: {},
                missingBehaviour: "message",
                missingTranslationPrefix: ""
            };
        return t.reset = function() {
            this.defaultLocale = c.defaultLocale, this.locale = c.locale, this.defaultSeparator = c.defaultSeparator, this.placeholder = c.placeholder, this.fallbacks = c.fallbacks, this.translations = c.translations, this.missingBehaviour = c.missingBehaviour, this.missingTranslationPrefix = c.missingTranslationPrefix
        }, t.initializeOptions = function() {
            "undefined" == typeof this.defaultLocale && null !== this.defaultLocale && (this.defaultLocale = c.defaultLocale), "undefined" == typeof this.locale && null !== this.locale && (this.locale = c.locale), "undefined" == typeof this.defaultSeparator && null !== this.defaultSeparator && (this.defaultSeparator = c.defaultSeparator), "undefined" == typeof this.placeholder && null !== this.placeholder && (this.placeholder = c.placeholder), "undefined" == typeof this.fallbacks && null !== this.fallbacks && (this.fallbacks = c.fallbacks), "undefined" == typeof this.translations && null !== this.translations && (this.translations = c.translations)
        }, t.initializeOptions(), t.locales = {}, t.locales.get = function(e) {
            var i = this[e] || this[t.locale] || this["default"];
            return "function" == typeof i && (i = i(e)), i instanceof Array == !1 && (i = [i]), i
        }, t.locales["default"] = function(e) {
            var i, n = [],
                r = [];
            return e && n.push(e), !e && t.locale && n.push(t.locale), t.fallbacks && t.defaultLocale && n.push(t.defaultLocale), n.forEach(function(e) {
                i = e.split("-")[0], ~r.indexOf(e) || r.push(e), t.fallbacks && i && i !== e && !~r.indexOf(i) && r.push(i)
            }), n.length || n.push("en"), r
        }, t.pluralization = {}, t.pluralization.get = function(e) {
            return this[e] || this[t.locale] || this["default"]
        }, t.pluralization["default"] = function(e) {
            switch (e) {
                case 0:
                    return ["zero", "other"];
                case 1:
                    return ["one"];
                default:
                    return ["other"]
            }
        }, t.currentLocale = function() {
            return this.locale || this.defaultLocale
        }, t.isSet = function(e) {
            return void 0 !== e && null !== e
        }, t.lookup = function(e, t) {
            t = this.prepareOptions(t);
            var i, n, r, s = this.locales.get(t.locale).slice();
            for (s[0], e = this.getFullScope(e, t); s.length;)
                if (i = s.shift(), n = e.split(this.defaultSeparator), r = this.translations[i]) {
                    for (; n.length && (r = r[n.shift()], void 0 !== r && null !== r););
                    if (void 0 !== r && null !== r) return r
                }
            return this.isSet(t.defaultValue) ? t.defaultValue : void 0
        }, t.meridian = function() {
            var e = this.lookup("time"),
                t = this.lookup("date");
            return e && e.am && e.pm ? [e.am, e.pm] : t && t.meridian ? t.meridian : r.meridian
        }, t.prepareOptions = function() {
            for (var e, t = i.call(arguments), n = {}; t.length;)
                if (e = t.shift(), "object" == typeof e)
                    for (var r in e) e.hasOwnProperty(r) && (this.isSet(n[r]) || (n[r] = e[r]));
            return n
        }, t.createTranslationOptions = function(e, t) {
            var i = [{
                scope: e
            }];
            return this.isSet(t.defaults) && (i = i.concat(t.defaults)), this.isSet(t.defaultValue) && (i.push({
                message: t.defaultValue
            }), delete t.defaultValue), i
        }, t.translate = function(e, t) {
            t = this.prepareOptions(t);
            var i, n = this.createTranslationOptions(e, t),
                r = n.some(function(e) {
                    return this.isSet(e.scope) ? i = this.lookup(e.scope, t) : this.isSet(e.message) && (i = e.message), void 0 !== i && null !== i ? !0 : void 0
                }, this);
            return r ? ("string" == typeof i ? i = this.interpolate(i, t) : i instanceof Object && this.isSet(t.count) && (i = this.pluralize(t.count, i, t)), i) : this.missingTranslation(e, t)
        }, t.interpolate = function(e, t) {
            t = this.prepareOptions(t);
            var i, n, r, s, a = e.match(this.placeholder);
            if (!a) return e;
            for (var n; a.length;) i = a.shift(), r = i.replace(this.placeholder, "$1"), n = this.isSet(t[r]) ? t[r].toString().replace(/\$/gm, "_#$#_") : r in t ? this.nullPlaceholder(i, e) : this.missingPlaceholder(i, e), s = new RegExp(i.replace(/\{/gm, "\\{").replace(/\}/gm, "\\}")), e = e.replace(s, n);
            return e.replace(/_#\$#_/g, "$")
        }, t.pluralize = function(e, t, i) {
            i = this.prepareOptions(i);
            var n, r, s, a, o;
            if (n = t instanceof Object ? t : this.lookup(t, i), !n) return this.missingTranslation(t, i);
            for (r = this.pluralization.get(i.locale), s = r(e); s.length;)
                if (a = s.shift(), this.isSet(n[a])) {
                    o = n[a];
                    break
                }
            return i.count = String(e), this.interpolate(o, i)
        }, t.missingTranslation = function(e, t) {
            if ("guess" == this.missingBehaviour) {
                var i = e.split(".").slice(-1)[0];
                return (this.missingTranslationPrefix.length > 0 ? this.missingTranslationPrefix : "") + i.replace("_", " ").replace(/([a-z])([A-Z])/g, function(e, t, i) {
                    return t + " " + i.toLowerCase()
                })
            }
            var n = this.getFullScope(e, t),
                r = [this.currentLocale(), n].join(this.defaultSeparator);
            return '[missing "' + r + '" translation]'
        }, t.missingPlaceholder = function(e) {
            return "[missing " + e + " value]"
        }, t.nullPlaceholder = function() {
            return t.missingPlaceholder.apply(t, arguments)
        }, t.toNumber = function(e, t) {
            t = this.prepareOptions(t, this.lookup("number.format"), s);
            var i, n, r = 0 > e,
                a = Math.abs(e).toFixed(t.precision).toString(),
                o = a.split("."),
                l = [],
                c = t.format || "%n",
                d = r ? "-" : "";
            for (e = o[0], i = o[1]; e.length > 0;) l.unshift(e.substr(Math.max(0, e.length - 3), 3)), e = e.substr(0, e.length - 3);
            return n = l.join(t.delimiter), t.strip_insignificant_zeros && i && (i = i.replace(/0+$/, "")), t.precision > 0 && i && (n += t.separator + i), c = t.sign_first ? "%s" + c : c.replace("%n", "%s%n"), n = c.replace("%u", t.unit).replace("%n", n).replace("%s", d)
        }, t.toCurrency = function(e, t) {
            return t = this.prepareOptions(t, this.lookup("number.currency.format"), this.lookup("number.format"), a), this.toNumber(e, t)
        }, t.localize = function(e, t, i) {
            switch (i || (i = {}), e) {
                case "currency":
                    return this.toCurrency(t);
                case "number":
                    return e = this.lookup("number.format"), this.toNumber(t, e);
                case "percentage":
                    return this.toPercentage(t);
                default:
                    var n;
                    return n = e.match(/^(date|time)/) ? this.toTime(e, t) : t.toString(), this.interpolate(n, i)
            }
        }, t.parseDate = function(e) {
            var t, i, n;
            if ("object" == typeof e) return e;
            if (t = e.toString().match(/(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2}):(\d{2})([\.,]\d{1,3})?)?(Z|\+00:?00)?/)) {
                for (var r = 1; 6 >= r; r++) t[r] = parseInt(t[r], 10) || 0;
                t[2] -= 1, n = t[7] ? 1e3 * ("0" + t[7]) : null, i = t[8] ? new Date(Date.UTC(t[1], t[2], t[3], t[4], t[5], t[6], n)) : new Date(t[1], t[2], t[3], t[4], t[5], t[6], n)
            } else "number" == typeof e ? (i = new Date, i.setTime(e)) : e.match(/([A-Z][a-z]{2}) ([A-Z][a-z]{2}) (\d+) (\d+:\d+:\d+) ([+-]\d+) (\d+)/) ? (i = new Date, i.setTime(Date.parse([RegExp.$1, RegExp.$2, RegExp.$3, RegExp.$6, RegExp.$4, RegExp.$5].join(" ")))) : e.match(/\d+ \d+:\d+:\d+ [+-]\d+ \d+/) ? (i = new Date, i.setTime(Date.parse(e))) : (i = new Date, i.setTime(Date.parse(e)));
            return i
        }, t.strftime = function(e, i) {
            var s = this.lookup("date"),
                a = t.meridian();
            s || (s = {}), s = this.prepareOptions(s, r);
            var o = e.getDay(),
                l = e.getDate(),
                c = e.getFullYear(),
                d = e.getMonth() + 1,
                u = e.getHours(),
                h = u,
                p = u > 11 ? 1 : 0,
                f = e.getSeconds(),
                m = e.getMinutes(),
                g = e.getTimezoneOffset(),
                _ = Math.floor(Math.abs(g / 60)),
                v = Math.abs(g) - 60 * _,
                y = (g > 0 ? "-" : "+") + (_.toString().length < 2 ? "0" + _ : _) + (v.toString().length < 2 ? "0" + v : v);
            return h > 12 ? h -= 12 : 0 === h && (h = 12), i = i.replace("%a", s.abbr_day_names[o]), i = i.replace("%A", s.day_names[o]), i = i.replace("%b", s.abbr_month_names[d]), i = i.replace("%B", s.month_names[d]), i = i.replace("%d", n(l)), i = i.replace("%e", l), i = i.replace("%-d", l), i = i.replace("%H", n(u)), i = i.replace("%-H", u), i = i.replace("%I", n(h)), i = i.replace("%-I", h), i = i.replace("%m", n(d)), i = i.replace("%-m", d), i = i.replace("%M", n(m)), i = i.replace("%-M", m), i = i.replace("%p", a[p]), i = i.replace("%S", n(f)), i = i.replace("%-S", f), i = i.replace("%w", o), i = i.replace("%y", n(c)), i = i.replace("%-y", n(c).replace(/^0+/, "")), i = i.replace("%Y", c), i = i.replace("%z", y)
        }, t.toTime = function(e, t) {
            var i = this.parseDate(t),
                n = this.lookup(e);
            return i.toString().match(/invalid/i) ? i.toString() : n ? this.strftime(i, n) : i.toString()
        }, t.toPercentage = function(e, t) {
            return t = this.prepareOptions(t, this.lookup("number.percentage.format"), this.lookup("number.format"), o), this.toNumber(e, t)
        }, t.toHumanSize = function(e, t) {
            for (var i, n, r = 1024, s = e, a = 0; s >= r && 4 > a;) s /= r, a += 1;
            return 0 === a ? (i = this.t("number.human.storage_units.units.byte", {
                count: s
            }), n = 0) : (i = this.t("number.human.storage_units.units." + l[a]), n = 0 === s - Math.floor(s) ? 0 : 1), t = this.prepareOptions(t, {
                unit: i,
                precision: n,
                format: "%n%u",
                delimiter: ""
            }), this.toNumber(s, t)
        }, t.getFullScope = function(e, t) {
            return t = this.prepareOptions(t), e.constructor === Array && (e = e.join(this.defaultSeparator)), t.scope && (e = [t.scope, e].join(this.defaultSeparator)), e
        }, t.t = t.translate, t.l = t.localize, t.p = t.pluralize, t
    }), Array.prototype.indexOf || (Array.prototype.indexOf = function(e) {
        "use strict";
        if (null == this) throw new TypeError;
        var t = Object(this),
            i = t.length >>> 0;
        if (0 === i) return -1;
        var n = 0;
        if (arguments.length > 1 && (n = Number(arguments[1]), n != n ? n = 0 : 0 != n && 1 / 0 != n && n != -1 / 0 && (n = (n > 0 || -1) * Math.floor(Math.abs(n)))), n >= i) return -1;
        for (var r = n >= 0 ? n : Math.max(i - Math.abs(n), 0); i > r; r++)
            if (r in t && t[r] === e) return r;
        return -1
    }), Array.prototype.forEach || (Array.prototype.forEach = function(e, t) {
        var i, n;
        if (null == this) throw new TypeError("this is null or not defined");
        var r = Object(this),
            s = r.length >>> 0;
        if ("[object Function]" !== {}.toString.call(e)) throw new TypeError(e + " is not a function");
        for (t && (i = t), n = 0; s > n;) {
            var a;
            Object.prototype.hasOwnProperty.call(r, n) && (a = r[n], e.call(i, a, n, r)), n++
        }
    }), Array.prototype.some || (Array.prototype.some = function(e) {
        "use strict";
        if (void 0 === this || null === this) throw new TypeError;
        var t = Object(this),
            i = t.length >>> 0;
        if ("function" != typeof e) throw new TypeError;
        for (var n = arguments.length >= 2 ? arguments[1] : void 0, r = 0; i > r; r++)
            if (r in t && e.call(n, t[r], r, t)) return !0;
        return !1
    }),
    function(e) {
        "undefined" != typeof module && module.exports ? e(require("i18n")) : "function" == typeof define && define.amd ? define(["i18n"], e) : e(this.I18n)
    }(function(e) {
        "use strict";
        e.translations = {
            en: {},
            fr: {}
        }
    });
var PopulateFormData = PopulateFormData || function() {
    function e(e) {
        var e = e || {};
        return PopulateData.fromStore("#group_id", "group", !0), PopulateData.fromStore("#responder_id", "agent", !0), e.isAjax ? (n(e.url, e.defaultKey, function(i) {
            o(i, e), t(e.defaultKey)
        }), void 0) : (o(e.data, e), t(), void 0)
    }

    function t(e) {
        var t = ["deleted", "spam", "monitored_by"];
        e && -1 != t.indexOf(e) || (getFilterData(), jQuery("#FilterOptions input[name=data_hash]").val(query_hash.toJSON()))
    }

    function i(e, t) {
        var i, n = {};
        for (var r in e) i = t[r] ? t[r] : r, n[i] = e[r];
        return n
    }

    function n(e, t, i) {
        var n = r(t),
            s = {};
        return s[n] = t, jQuery.getJSON(e, s, i)
    }

    function r(e) {
        return "number" == typeof parseInt(e) ? "filter_key" : "filter_name"
    }

    function s(e) {
        return Object.keys(e)
    }

    function a(e, t) {
        e >= 0 && (jQuery("#agentSort").parent().data("fromFilters", !0), jQuery(".shared_sort_menu .agent_mode[mode='" + e + "']").trigger("click", ["customTrigger"])), t >= 0 && (jQuery("#groupSort").parent().data("fromFilters", !0), jQuery(".shared_sort_menu .group_mode[mode='" + t + "']").trigger("click", ["customTrigger"]))
    }

    function o(e, t) {
        var n, r, o, d, u;
        r = t.isAjax ? e.conditions : i(e, t.fieldMap), t.sharedOwnershipFlag && a(e.agent_mode || 0, e.group_mode || 0), n = l(t.defaultKey), o = jQuery.extend(n, r), d = s(o), u = e.meta_data, n.defaultDateRange = t.defaultDateRange, d.each(function(e) {
            "spam" !== e && "deleted" !== e && c(e, o, u)
        }), jQuery(".sloading.filter-loading").hide()
    }

    function l(e) {
        var t, i = {};
        return jQuery(".ff_item").each(function() {
            t = jQuery(this).attr("condition"), i[t] = "", "created_at" == t && (i[t] = "all_tickets" == e ? "last_month" : "any_time")
        }), i
    }

    function c(e, t, i) {
        var n = jQuery("[condition='" + e + "']"),
            r = n.data(),
            s = t[e].toString().split(",");
        if (r) switch (r.domtype) {
            case "nested_field":
                jQuery("[condition='" + r.id + "']").children("select").val(s).trigger("change", ["customTrigger"]);
                break;
            case "dropdown":
                jQuery("[condition='" + r.id + "']").find("input").prop("checked", !1), s.each(function(e) {
                    jQuery("[condition='" + r.id + "']").find('input[value="' + e + '"]').prop("checked", !0)
                });
                break;
            case "multi_select":
                0 == jQuery("#" + e).length ? jQuery("[condition='" + r.id + "']").children("select").val(s).trigger("change.select2") : jQuery("#" + e).val(s).trigger("change.select2");
                break;
            case "association_type":
                jQuery("[condition='" + r.id + "']").children("select").val(s).trigger("change.select2");
                break;
            case "single_select":
                var a = s[0].split("-");
                2 == a.length ? (jQuery("#created_date_range").val(s), jQuery("#" + e).data("selectedValue", s).val("set_date").trigger("change.select2"), jQuery("#div_ff_created_date_range").show()) : (a = t.defaultDateRange.split("-"), jQuery("#" + e).val(s).trigger("change.select2"), jQuery("#div_ff_created_date_range").hide(), jQuery("#created_date_range").val(""));
                try {
                    var o = jQuery("#created_date_range").data("bootstrapdaterangepicker");
                    o.setStartDate(a[0]), o.setEndDate(a[1])
                } catch (l) {
                    console.log(l)
                }
                break;
            case "requester":
            case "customers":
            case "tags":
                i && i[r.id] ? jQuery("#" + r.domtype + "_filter").select2("data", i[r.id]) : jQuery("#" + r.domtype + "_filter").select2("data", "");
                break;
            default:
                jQuery("#" + e).val(s).trigger("change.select2")
        }
    }
    return {
        init: e
    }
}();