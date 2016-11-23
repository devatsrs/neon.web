/* ==========================================================
 * bootstrap-affix.js v2.1.1
 * http://twitter.github.com/bootstrap/javascript.html#affix
 * ==========================================================
 * Copyright 2012 Twitter, Inc.
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
 * ========================================================== */
function get_common_ancestor(e, t) {
    $parentsa = $(e).parents(), $parentsb = $(t).parents();
    var i = null;
    return $parentsa.each(function() {
        var e = this;
        return $parentsb.each(function() {
            return e == this ? (i = this, !1) : void 0
        }), i ? !1 : void 0
    }), i
}! function(e) {
    "use strict";
    if (!e.fn.affix) {
        var t = function(t, i) {
            this.options = e.extend({}, e.fn.affix.defaults, i), this.$window = e(window).on("scroll.affix.data-api", e.proxy(this.checkPosition, this)), this.$element = e(t), this.checkPosition()
        };
        t.prototype.checkPosition = function() {
            if (this.$element.is(":visible")) {
                var t, i = e(document).height(),
                    n = this.$window.scrollTop(),
                    r = this.$element.offset(),
                    s = this.options.offset,
                    a = s.bottom,
                    o = s.top,
                    l = "affix affix-top affix-bottom";
                "object" != typeof s && (a = o = s), "function" == typeof o && (o = s.top()), "function" == typeof a && (a = s.bottom()), t = null != this.unpin && n + this.unpin <= r.top ? !1 : null != a && r.top + this.$element.height() >= i - a ? "bottom" : null != o && o >= n ? "top" : !1, this.affixed !== t && (this.affixed = t, this.unpin = "bottom" == t ? r.top - n : null, this.$element.removeClass(l).addClass("affix" + (t ? "-" + t : "")))
            }
        }, e.fn.affix = function(i) {
            return this.each(function() {
                var n = e(this),
                    r = n.data("affix"),
                    s = "object" == typeof i && i;
                r || n.data("affix", r = new t(this, s)), "string" == typeof i && r[i]()
            })
        }, e.fn.affix.Constructor = t, e.fn.affix.defaults = {
            offset: 0
        }, e(window).on("load", function() {
            e('[data-spy="affix"]').each(function() {
                var t = e(this),
                    i = t.data();
                i.offset = i.offset || {}, i.offsetBottom && (i.offset.bottom = i.offsetBottom), i.offsetTop && (i.offset.top = i.offsetTop), t.affix(i)
            })
        })
    }
}(window.jQuery),
/* ============================================================
 * bootstrap-button.js v2.1.1
 * http://twitter.github.com/bootstrap/javascript.html#buttons
 * ============================================================
 * Copyright 2012 Twitter, Inc.
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
 * ============================================================ */
! function(e) {
    "use strict";
    var t = function(t, i) {
        this.$element = e(t), this.options = e.extend({}, e.fn.button.defaults, i)
    };
    t.prototype.setState = function(e) {
        var t = "disabled",
            i = this.$element,
            n = i.data(),
            r = i.is("input") ? "val" : "html";
        e += "Text", n.resetText || i.data("resetText", i[r]()), i[r](n[e] || this.options[e]), setTimeout(function() {
            "loadingText" == e ? i.addClass(t).attr(t, t) : i.removeClass(t).removeAttr(t)
        }, 0)
    }, t.prototype.toggle = function() {
        var e = this.$element.closest('[data-toggle="buttons-radio"]');
        e && e.find(".active").removeClass("active"), this.$element.toggleClass("active")
    }, e.fn.button = function(i) {
        return this.each(function() {
            var n = e(this),
                r = n.data("button"),
                s = "object" == typeof i && i;
            r || n.data("button", r = new t(this, s)), "toggle" == i ? r.toggle() : i && r.setState(i)
        })
    }, e.fn.button.defaults = {
        loadingText: "loading..."
    }, e.fn.button.Constructor = t, e(function() {
        e("body").on("click.button.data-api", "[data-toggle^=button]", function(t) {
            var i = e(t.target);
            i.hasClass("btn") || (i = i.closest(".btn")), i.button("toggle")
        })
    })
}(window.jQuery),
/* ============================================================
 * bootstrap-dropdown.js v2.3.1
 * http://twitter.github.com/bootstrap/javascript.html#dropdowns
 * ============================================================
 * Copyright 2012 Twitter, Inc.
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
 * ============================================================ */
! function(e) {
    "use strict";

    function t() {
        e(n).each(function() {
            i(e(this)).removeClass("open"), i(e(this)).find(".dropdown-menu").toggle().toggle()[0].style.display = "none"
        })
    }

    function i(t) {
        var i, n = t.attr("data-target");
        return n || (n = t.attr("href"), n = n && /#[A-Za-z]/.test(n) && n.replace(/.*(?=#[^\s]*$)/, "")), i = n && e(n), i && i.length || (i = t.parent()), i
    }
    if (!e.fn.dropdown) {
        var n = "[data-toggle=dropdown]",
            r = function(t) {
                var i = e(t).on("click.dropdown.data-api", this.toggle);
                e("html").on("click.dropdown.data-api", function() {
                    i.parent().removeClass("open")
                })
            };
        r.prototype = {
            constructor: r,
            toggle: function() {
                var n, r, s = e(this);
                if (!s.is(".disabled, :disabled")) return n = i(s), r = n.hasClass("open"), t(), r ? n.find(".dropdown-menu").toggle().toggle()[0].style.display = "none" : (n.toggleClass("open"), n.find(".dropdown-menu").toggle().toggle()[0].style.display = "block"), s.focus(), !1
            },
            keydown: function(t) {
                var r, s, a, o, l;
                if (/(38|40|27)/.test(t.keyCode) && (r = e(this), t.preventDefault(), t.stopPropagation(), !r.is(".disabled, :disabled"))) {
                    if (a = i(r), o = a.hasClass("open"), !o || o && 27 == t.keyCode) return 27 == t.which && a.find(n).focus(), r.click();
                    s = e("[role=menu] li:not(.divider):visible a", a), s.length && (l = s.index(s.filter(":focus")), 38 == t.keyCode && l > 0 && l--, 40 == t.keyCode && l < s.length - 1 && l++, ~l || (l = 0), s.eq(l).focus())
                }
            }
        };
        var s = e.fn.dropdown;
        e.fn.dropdown = function(t) {
            return this.each(function() {
                var i = e(this),
                    n = i.data("dropdown");
                n || i.data("dropdown", n = new r(this)), "string" == typeof t && n[t].call(i)
            })
        }, e.fn.dropdown.Constructor = r, e.fn.dropdown.noConflict = function() {
            return e.fn.dropdown = s, this
        }, e(document).on("click.dropdown.data-api", t).on("click.dropdown.data-api", ".dropdown form", function(e) {
            e.stopPropagation()
        }).on("click.dropdown-menu", function(e) {
            e.stopPropagation()
        }).on("click.dropdown.data-api", n, r.prototype.toggle).on("keydown.dropdown.data-api", n + ", [role=menu]", r.prototype.keydown)
    }
}(window.jQuery),
/* =========================================================
 * bootstrap-modal.js v2.1.1
 * http://twitter.github.com/bootstrap/javascript.html#modals
 * =========================================================
 * Copyright 2012 Twitter, Inc.
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
 * ========================================================= */
! function(e) {
    "use strict";
    if (!e.fn.modal) {
        var t = function(t, i) {
            this.options = i, this.$element = e(t).delegate('[data-dismiss="modal"]', "click.dismiss.modal", e.proxy(this.hide, this)), this.$element.data("source") && (this.$source = this.$element.data("source")), this.options.remote && this.$element.find(".modal-body").ajaxLoad({
                url: this.options.remote
            })
        };
        t.prototype = {
            constructor: t,
            toggle: function() {
                return this[this.isShown ? "hide" : "show"]()
            },
            show: function() {
                var t = this,
                    i = e.Event("show");
                this.$element.trigger(i), this.isShown || i.isDefaultPrevented() || (e("body").addClass("modal-open"), this.isShown = !0, this.escape(), this.backdrop(function() {
                    var i = e.support.transition && t.$element.hasClass("fade");
                    t.$element.parent().length || t.$element.appendTo(document.body), t.$element.show(), i && t.$element[0].offsetWidth, t.$element.addClass("in").attr("aria-hidden", !1).focus(), t.enforceFocus(), i ? t.$element.one(e.support.transition.end, function() {
                        t.$element.trigger("shown")
                    }) : t.$element.trigger("shown")
                }))
            },
            hide: function(t) {
                this.options.destroyOnClose && this.destroy(), t && t.preventDefault(), t = e.Event("hide"), this.$element.trigger(t), this.isShown && !t.isDefaultPrevented() && (this.isShown = !1, e("body").removeClass("modal-open"), this.escape(), e(document).off("focusin.modal"), this.$element.removeClass("in").attr("aria-hidden", !0), e.support.transition && this.$element.hasClass("fade") ? this.hideWithTransition() : this.hideModal())
            },
            enforceFocus: function() {
                var t = this;
                e(document).on("focusin.modal", function(e) {
                    t.$element[0] === e.target || t.$element.has(e.target).length || t.$element.focus()
                })
            },
            escape: function() {
                var e = this;
                this.isShown && this.options.keyboard ? this.$element.on("keyup.dismiss.modal", function(t) {
                    27 == t.which && e.hide()
                }) : this.isShown || this.$element.off("keyup.dismiss.modal")
            },
            hideWithTransition: function() {
                var t = this,
                    i = setTimeout(function() {
                        t.$element.off(e.support.transition.end), t.hideModal()
                    }, 500);
                this.$element.one(e.support.transition.end, function() {
                    clearTimeout(i), t.hideModal()
                })
            },
            hideModal: function() {
                this.$element.hide().trigger("hidden"), this.backdrop()
            },
            removeBackdrop: function() {
                this.$backdrop.remove(), this.$backdrop = null
            },
            backdrop: function(t) {
                var i = this.$element.hasClass("fade") ? "fade" : "";
                if (this.isShown && this.options.backdrop) {
                    var n = e.support.transition && i;
                    this.$backdrop = e('<div class="modal-backdrop ' + i + '" />').appendTo(document.body), "static" != this.options.backdrop && this.$backdrop.click(e.proxy(this.hide, this)), n && this.$backdrop[0].offsetWidth, this.$backdrop.addClass("in"), n ? this.$backdrop.one(e.support.transition.end, t) : t()
                } else !this.isShown && this.$backdrop ? (this.$backdrop.removeClass("in"), e.support.transition && this.$element.hasClass("fade") ? this.$backdrop.one(e.support.transition.end, e.proxy(this.removeBackdrop, this)) : this.removeBackdrop()) : t && t()
            },
            destroy: function() {
                this.$source && this.$source.data("freshdialog").destroy(), this.$element.off("dismiss.modal").removeData("model").removeData("source").remove()
            }
        }, e.fn.modal = function(i) {
            return this.each(function() {
                var n = e(this),
                    r = n.data("modal"),
                    s = e.extend({}, e.fn.modal.defaults, n.data(), "object" == typeof i && i);
                r || n.data("modal", r = new t(this, s)), "string" == typeof i ? r[i]() : s.show && r.show()
            })
        }, e.fn.modal.defaults = {
            backdrop: !0,
            keyboard: !0,
            show: !0,
            destroyOnClose: !1
        }, e.fn.modal.Constructor = t, e(function() {
            e("body").on("click.modal.data-api", '[data-toggle="modal"]', function(t) {
                var i = e(this),
                    n = i.attr("href"),
                    r = e(i.attr("data-target") || n && n.replace(/.*(?=#[^\s]+$)/, "")),
                    s = r.data("modal") ? "toggle" : e.extend({
                        remote: !/#/.test(n) && n
                    }, r.data(), i.data());
                t.preventDefault(), r.modal(s).one("hide", function() {
                    i.focus()
                })
            })
        })
    }
}(window.jQuery), ! function($) {
    "use strict";
    var Freshdialog = function(e, t, i) {
        var n = {};
        null !== e && (this.$element = e, n = this.$element.data()), this.options = $.extend({}, $.fn.freshdialog.defaults, t, n), this.options.title = this.options.title || this.options.modalTitle, this.$dialogid = this.options.targetId.substring(1), this.$content = $(this.options.targetId);
        var r = "rtl" == $("html").attr("dir") ? "marginRight" : "marginLeft";
        this.$placement = {
            width: this.options.width
        }, "slide" == !this.options.modalType && (this.$placement[r] = -(parseInt(this.options.width) / 2)), this.$dynamicTarget = $('<div class="modal fade" role="dialog" aria-hidden="true"></div>').attr("id", this.$dialogid).addClass(this.options.classes).css(this.$placement).appendTo("body"), "slide" == this.options.modalType && (this.$dynamicTarget.addClass("slider-modal"), this.$element.data("backdrop", !1)), "" != this.options.templateHeader && (this.dialogTitle = i || this.options.title, this.$dynamicTarget.append(this.options.templateHeader).find(".modal-title").attr("title", this.dialogTitle).html(this.dialogTitle), 1 == this.options.showClose && this.$dynamicTarget.find(".modal-header").prepend('<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>')), this.$body = $(this.options.templateBody), this.$content.get(0) && this.$body.html(this.$content.attr("id", "").show()).attr("id", this.$dialogid + "-content"), this.$dynamicTarget.append(this.$body), "" != this.options.templateFooter && (this.$closeBtn = $('<a href="#" data-dismiss="modal" class="btn">' + this.options.closeLabel + "</a>").attr("id", this.$dialogid + "-cancel"), this.$submitBtn = $('<a href="#" data-submit="modal" class="' + this.options.submitClass + '">' + this.options.submitLabel + "</a>").attr("id", this.$dialogid + "-submit"), "" != this.options.submitLoading && this.$submitBtn.data("loadingText", this.options.submitLoading), this.$footer = $(this.options.templateFooter).append(this.$closeBtn).append(this.$submitBtn).appendTo(this.$dynamicTarget)), this.$dynamicTarget.delegate('[data-submit="modal"]:not([disabled])', "click.submit.modal", $.proxy(this.formSubmit, this))
    };
    Freshdialog.prototype = {
        constructor: Freshdialog,
        formSubmit: function(e) {
            e && e.preventDefault();
            var t = this.$dynamicTarget.find("form:first");
            t.get(0) && t.valid() && ($(t).trigger("dialog:submit"), "" != this.options.submitLoading && this.$submitBtn.button("loading"), t.submit()), this.options.closeOnSubmit && this.$dynamicTarget.modal("hide")
        },
        destroy: function() {
            $(this.$body.html()).appendTo("body").attr("id", this.$dialogid).hide(), void 0 !== this.$element && this.$element.removeData("freshdialog"), this.$dynamicTarget.off("submit.modal")
        }
    }, $.fn.freshdialog = function(option) {
        return this.each(function() {
            var $this = $(this),
                data = $this.data("freshdialog"),
                options = "object" == typeof option && option,
                allowed = eval($this.data("check") || !0);
            !data && allowed && $this.data("freshdialog", data = new Freshdialog($this, options, this.getAttribute("title"))), "string" == typeof option && data[option]()
        })
    }, $.freshdialog = function(e) {
        var t, i = "object" == typeof e && e,
            n = new Freshdialog(null, i);
        return t = $(i.targetId), t.data("freshdialog", n), t.data("source", t), t.modal(i), n
    }, $.fn.freshdialog.defaults = {
        width: "710px",
        title: "",
        classes: "",
        closeOnSubmit: !1,
        keyboard: !0,
        modalType: "modal",
        templateHeader: '<div class="modal-header"><h3 class="ellipsis modal-title"></h3></div>',
        templateBody: '<div class="modal-body"><div class="sloading loading-small loading-block"></div></div>',
        templateFooter: '<div class="modal-footer"></div>',
        submitLabel: "Submit",
        submitClass: "btn btn-primary",
        submitLoading: "",
        closeLabel: "Close",
        showClose: !0,
        destroyOnClose: !1
    }, $.fn.freshdialog.Constructor = Freshdialog, $(document).on("click.freshdialog.data-api", '[rel="freshdialog"]', function(e) {
        e.preventDefault();
        var t = $(this),
            i = t.attr("href");
        if (t.data("lazyload")) {
            var n = $(t.data("target") + " textarea[rel=lazyload]").first().val();
            $(t.data("target")).hide().html(n)
        }
        if (!t.data("freshdialog")) {
            var r = t.attr("data-target") || i && i.replace(/.*(?=#[^\s]+$)/, "");
            if (t.data("targetId", r), "slide" == t.data("modalType") && $(t.data("targetId")).modal("hide"), t.freshdialog(), t.data("group")) {
                var s = $("[data-group=" + t.data("group") + "]");
                s.data({
                    targetId: r,
                    freshdialog: t.data("freshdialog")
                })
            }
        }
        var a = $($(this).data("targetId")),
            o = a.data("modal") ? "toggle" : $.extend({
                remote: !/#/.test(i) && i
            }, a.data(), t.data());
        a.data("source", t), a.data("modal") ? a.modal("toggle") : a.modal(o), "slide" == t.data("modalType") && $("body").removeClass("modal-open")
    })
}(window.jQuery),
/* ===================================================
 * bootstrap-transition.js v2.1.1
 * http://twitter.github.com/bootstrap/javascript.html#transitions
 * ===================================================
 * Copyright 2012 Twitter, Inc.
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
 * ========================================================== */
! function(e) {
    e(function() {
        "use strict";
        e.support.transition = function() {
            var e = function() {
                var e, t = document.createElement("bootstrap"),
                    i = {
                        WebkitTransition: "webkitTransitionEnd",
                        MozTransition: "transitionend",
                        OTransition: "oTransitionEnd otransitionend",
                        transition: "transitionend"
                    };
                for (e in i)
                    if (void 0 !== t.style[e]) return i[e]
            }();
            return e && {
                end: e
            }
        }()
    })
}(window.jQuery),
/* ========================================================
 * bootstrap-tab.js v2.1.1
 * http://twitter.github.com/bootstrap/javascript.html#tabs
 * ========================================================
 * Copyright 2012 Twitter, Inc.
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
    if (!e.fn.tab) {
        var t = function(t) {
            this.element = e(t)
        };
        t.prototype = {
            constructor: t,
            show: function() {
                var t, i, n, r = this.element,
                    s = r.closest("ul:not(.dropdown-menu)"),
                    a = r.attr("data-target");
                a || (a = r.attr("href"), a = a && a.replace(/.*(?=#[^\s]*$)/, "")), r.parent("li").hasClass("active") || (t = s.find(".active a").last()[0], n = e.Event("show", {
                    relatedTarget: t
                }), r.data("hash-url") !== !1 && (window.location.hash = a), r.trigger(n), n.isDefaultPrevented() || (i = e(a), this.activate(r.parent("li"), s), this.activate(i, i.parent(), function() {
                    r.trigger({
                        type: "shown",
                        relatedTarget: t
                    })
                })))
            },
            activate: function(t, i, n) {
                function r() {
                    s.removeClass("active").find("> .dropdown-menu > .active").removeClass("active"), t.addClass("active"), a ? (t[0].offsetWidth, t.addClass("in")) : t.removeClass("fade"), t.parent(".dropdown-menu") && t.closest("li.dropdown").addClass("active"), n && n()
                }
                var s = i.find("> .active"),
                    a = n && e.support.transition && s.hasClass("fade");
                a ? s.one(e.support.transition.end, r) : r(), s.removeClass("in")
            }
        }, e.fn.tab = function(i) {
            return this.each(function() {
                var n = e(this),
                    r = n.data("tab");
                r || n.data("tab", r = new t(this)), "string" == typeof i && r[i]()
            })
        }, e.fn.tab.Constructor = t, e(function() {
            e("body").on("click.tab.data-api", '[data-toggle="tab"], [data-toggle="pill"]', function(t) {
                t.preventDefault(), e(this).tab("show")
            })
        })
    }
}(window.jQuery),
/* ==========================================================
 * bootstrap-carousel.js v2.1.1
 * http://twitter.github.com/bootstrap/javascript.html#carousel
 * ==========================================================
 * Copyright 2012 Twitter, Inc.
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
 * ========================================================== */
! function(e) {
    "use strict";
    if (!e.fn.carousel) {
        var t = function(t, i) {
            this.$element = e(t), this.options = i, this.options.slide && this.slide(this.options.slide), "hover" == this.options.pause && this.$element.on("mouseenter", e.proxy(this.pause, this)).on("mouseleave", e.proxy(this.cycle, this))
        };
        t.prototype = {
            cycle: function(t) {
                return t || (this.paused = !1), this.options.interval && !this.paused && (this.interval = setInterval(e.proxy(this.next, this), this.options.interval)), this
            },
            to: function(t) {
                var i = this.$element.find(".item.active"),
                    n = i.parent().children(),
                    r = n.index(i),
                    s = this;
                if (!(t > n.length - 1 || 0 > t)) return this.sliding ? this.$element.one("slid", function() {
                    s.to(t)
                }) : r == t ? this.pause().cycle() : this.slide(t > r ? "next" : "prev", e(n[t]))
            },
            pause: function(t) {
                return t || (this.paused = !0), this.$element.find(".next, .prev").length && e.support.transition.end && (this.$element.trigger(e.support.transition.end), this.cycle()), clearInterval(this.interval), this.interval = null, this
            },
            next: function() {
                return this.sliding ? void 0 : this.slide("next")
            },
            prev: function() {
                return this.sliding ? void 0 : this.slide("prev")
            },
            slide: function(t, i) {
                var n = this.$element.find(".item.active"),
                    r = i || n[t](),
                    s = this.interval,
                    a = "next" == t ? "left" : "right",
                    o = "next" == t ? "first" : "last",
                    l = this,
                    c = e.Event("slide", {
                        relatedTarget: r[0]
                    });
                if (this.sliding = !0, s && this.pause(), r = r.length ? r : this.$element.find(".item")[o](), !r.hasClass("active")) {
                    if (e.support.transition && this.$element.hasClass("slide")) {
                        if (this.$element.trigger(c), c.isDefaultPrevented()) return;
                        r.addClass(t), r[0].offsetWidth, n.addClass(a), r.addClass(a), this.$element.one(e.support.transition.end, function() {
                            r.removeClass([t, a].join(" ")).addClass("active"), n.removeClass(["active", a].join(" ")), l.sliding = !1, setTimeout(function() {
                                l.$element.trigger("slid")
                            }, 0)
                        })
                    } else {
                        if (this.$element.trigger(c), c.isDefaultPrevented()) return;
                        n.removeClass("active"), r.addClass("active"), this.sliding = !1, this.$element.trigger("slid")
                    }
                    return s && this.cycle(), this
                }
            }
        }, e.fn.carousel = function(i) {
            return this.each(function() {
                var n = e(this),
                    r = n.data("carousel"),
                    s = e.extend({}, e.fn.carousel.defaults, "object" == typeof i && i),
                    a = "string" == typeof i ? i : s.slide;
                r || n.data("carousel", r = new t(this, s)), "number" == typeof i ? r.to(i) : a ? r[a]() : s.interval && r.cycle()
            })
        }, e.fn.carousel.defaults = {
            interval: 5e3,
            pause: "hover"
        }, e.fn.carousel.Constructor = t, e(function() {
            e("body").on("click.carousel.data-api", "[data-slide]", function(t) {
                var i, n = e(this),
                    r = e(n.attr("data-target") || (i = n.attr("href")) && i.replace(/.*(?=#[^\s]+$)/, "")),
                    s = !r.data("modal") && e.extend({}, r.data(), n.data());
                r.carousel(s), t.preventDefault()
            })
        })
    }
}(window.jQuery),
function(e) {
    e.fn.slide = function(t) {
        var i = e.extend({
            width: !1,
            direction: "left",
            duration: "faster"
        }, t);
        return this.each(function() {
            function t(t) {
                t = t || e(n).data("sliderOpen"), t ? (e(n).addClass("slide-shadow", {
                    duration: i.duration,
                    easing: "easeOutExpo"
                }), $slider.css({
                    visibility: "visible"
                }), $slider.animate({
                    left: "0"
                }, {
                    duration: i.duration,
                    easing: "easeOutExpo"
                }), $parent.animate({
                    left: a + "%"
                }, {
                    duration: i.duration,
                    easing: "easeOutExpo"
                }), e(n).data("sliderOpen", !1)) : ($slider.animate({
                    left: "-" + a + "%"
                }, {
                    duration: i.duration,
                    easing: "easeOutExpo"
                }), $slider.css({
                    visibility: "hidden"
                }), $parent.animate({
                    left: "0"
                }, {
                    duration: i.duration,
                    easing: "easeOutExpo"
                }), e(n).removeClass("slide-shadow", {
                    duration: i.duration,
                    easing: "easeOutExpo"
                }), e(n).data("sliderOpen", !0))
            }
            $this = e(this), $slider = e($this.attr("href")), $parent = e($this.data("parent")), $slider.css({
                visibility: "hidden",
                display: "block"
            }), $parent.show();
            var n = get_common_ancestor($slider, $parent),
                r = e(n).outerWidth(!0);
            $parent.outerWidth(!0);
            var s = Math.max($parent.outerHeight(!0), $slider.outerHeight(!0)),
                a = i.width ? i.width : $slider.outerWidth(!0);
            a = 100 * (a / r), e(n).css({
                position: "relative",
                overflow: "hidden",
                padding: "0"
            }).data("sliderOpen", !0), $slider.css({
                left: "-" + a + "%",
                position: "absolute"
            }), $parent.css({
                left: "0",
                position: "relative",
                width: "100%",
                "min-height": s,
                height: "auto !important"
            }), $this.on("click", function(e) {
                e.preventDefault(), t()
            }), $slider.delegate("[data-dismiss=slider]", "click", function(e) {
                e.preventDefault(), t(!1)
            }), $slider.change(function() {
                $slider.height() > $parent.height() && $parent.css({
                    "min-height": $slider.height()
                })
            })
        })
    }
}(jQuery),
function(e) {
    "use strict";
    var t = function(t, i) {
        if (this.element = e(t), this.options = e.extend({}, e.fn.sticky.defaults, i, e(t).data()), this.parentEle = this.element.parent(), null != this.options.parent_selector && (this.parentEle = this.parentEle.closest(this.options.parent_selector)), !this.parentEle.length) throw "failed to find stick parent";
        this.fixed = !1, this.bottomed = !1, this.doRefresh = !1, this.spacer = e("<div />"), this.spacer.css("position", this.element.css("position")), this.last_pos = void 0, this.top = 0, this.height = 0, this.el_float = "none", this.offset = "", this.init()
    };
    t.prototype = {
        init: function() {
            e(window).on("touchmove.sticky", e.proxy(this.tick, this)), e(window).on("scroll.sticky", e.proxy(this.tick, this)), e(window).on("resize.sticky", e.proxy(this.recalc_and_tick, this)), e(document).on("recalc.sticky", e.proxy(this.recalc_and_tick, this)), this.recalc_and_tick()
        },
        recalc: function() {
            var e, t, i, n = this.parentEle,
                r = this.element;
            return e = parseInt(n.css("border-top-width"), 10), t = parseInt(n.css("padding-top"), 10), this.padding_bottom = parseInt(n.css("padding-bottom"), 10), this.parent_top = n.offset().top + e + t, this.parent_height = n.height(), i = this.fixed ? (this.fixed = !1, this.bottomed = !1, r.insertAfter(this.spacer).css({
                position: "",
                top: "",
                width: "",
                bottom: ""
            }), this.spacer.detach(), !0) : void 0, this.top = r.offset().top - parseInt(r.css("margin-top"), 10) - this.options.offset_top, this.height = r.outerHeight(!0), this.el_float = r.css("float"), this.spacer.css({
                width: r.outerWidth(!0),
                height: this.height,
                display: r.css("display"),
                "vertical-align": r.css("vertical-align"),
                "float": this.el_float
            }), i ? this.tick() : void 0
        },
        tick: function() {
            var t, i, n, r, s, a = this.element;
            return n = e(window).scrollTop(), null != this.last_pos && (i = n - this.last_pos), this.last_pos = n, this.fixed ? (r = n + this.height + this.options.offset_top > this.parent_height + this.parent_top, this.bottomed && !r && (this.bottomed = !1, a.css({
                position: "fixed",
                bottom: "",
                top: this.options.offset_top
            }).trigger("sticky_kit:unbottom")), n < this.top && (this.fixed = !1, this.offset = this.options.offset_top, ("left" === this.el_float || "right" === this.el_float) && a.insertAfter(this.spacer), this.spacer.detach(), t = {
                position: "",
                width: "",
                top: ""
            }, a.css(t).removeClass(this.options.sticky_class).trigger("sticky_kit:unstick")), this.options.inner_scrolling && (s = e(window).height(), this.height > s && (this.bottomed || (this.offset -= i, this.offset = Math.max(s - this.height, this.offset), this.offset = Math.min(this.options.offset_top, this.offset), this.fixed && a.css({
                top: this.offset + "px"
            }))))) : n > this.top && (this.fixed = !0, t = {
                position: "fixed",
                top: this.options.offset_top
            }, t.width = "border-box" === a.css("box-sizing") ? a.outerWidth() + "px" : a.width() + "px", a.css(t).addClass(this.options.sticky_class).after(this.spacer), ("left" === this.el_float || "right" === this.el_float) && this.spacer.append(a), a.trigger("sticky_kit:stick")), this.fixed && (null == r && (r = n + this.height + this.offset > this.parent_height + this.parent_top), !this.bottomed && r && !this.options.elm_bottom) ? this.doRefresh ? (this.doRefresh = !1, this.bottomed = !0, a.css({
                position: "absolute",
                bottom: this.padding_bottom,
                top: "auto"
            }).trigger("sticky_kit:bottom")) : (this.doRefresh = !0, this.recalc(), this.tick()) : void 0
        },
        recalc_and_tick: function() {
            return this.recalc(), this.tick()
        },
        detach: function() {
            return this.fixed ? (this.element.insertAfter(this.spacer).removeClass(this.options.sticky_class), this.spacer.remove()) : (e(window).off(".sticky"), this.element.off("sticky_kit:stick").off("sticky_kit:unstick"), this.element.removeData("sticky"), delete this.element, delete this.parentEle, delete e(this.parentEle).prevObject, void 0)
        }
    }, e.fn.sticky = function(i) {
        return this.each(function() {
            var n = e(this),
                r = n.data("sticky"),
                s = "object" == typeof i && i;
            r || n.data("sticky", r = new t(this, s)), "string" == typeof i && r[i]()
        })
    }, e.fn.sticky.defaults = {
        sticky_class: "is_stuck",
        offset_top: 0,
        parent_selector: void 0,
        inner_scrolling: !0,
        elm_bottom: !1
    }
}(window.jQuery), ! function(e) {
    "use strict";
    var t = function(e) {
        return null === e || e.attr("disabled") ? !1 : (this.$element = e, this.data = this.$element.data(), this.constructForm(), this.constructDialog(), void 0)
    };
    t.prototype = {
        constructor: t,
        constructForm: function() {
            this.createForm(), this.appendInputs(), this.appendDetails(), this.bindHandlers()
        },
        constructDialog: function() {
            this.checkParentDiv() && this.createModalDiv(), this.triggerDialog()
        },
        createForm: function() {
            this.form = e("<form />").attr("id", this.data.dialogId + "-form").attr("class", "delete-confirm-form").attr("action", this.data.destroyUrl).attr("method", "POST")
        },
        appendInputs: function() {
            this.form.append(e("<input />").attr("type", "hidden").attr("name", "_method").val("delete")), this.text_input = e("<input />").attr("name", "verify_title").attr("placeholder", this.data.itemTitle).attr("type", "text").attr("autocomplete", "off").attr("id", "check-title_" + this.data.dialogId), this.form.append(this.text_input)
        },
        appendDetails: function() {
            this.form.prepend(e("<p />").html(this.data.deleteTitleMsg)), this.form.append(e("<p />").html(this.data.deleteMsg))
        },
        bindHandlers: function() {
            this.form.on("submit.delete_confirm", e.proxy(this.handleSubmit, this)), this.text_input.on("keyup.delete_confirm", e.proxy(this.handleKeyup, this))
        },
        handleSubmit: function() {
            return this.checkTitle()
        },
        handleKeyup: function() {
            this.btnToggle(!this.checkTitle())
        },
        checkParentDiv: function() {
            return 0 == e("#" + this.data.dialogId).length
        },
        triggerDialog: function() {
            this.data.targetId = "#" + this.data.dialogId, e.freshdialog(this.data), this.hideSubmitInitial()
        },
        createModalDiv: function() {
            this.createParentDiv(), this.createContextDiv(), this.appendContextDetails()
        },
        createParentDiv: function() {
            this.parent_div = e("<div />").attr("id", "delete_confirm_dialogs").appendTo("body")
        },
        createContextDiv: function() {
            this.context_div = e("<div />").attr("id", this.data.dialogId).addClass("hide")
        },
        appendContextDetails: function() {
            this.createWarningDiv(), this.context_div.append(this.warning_div).append(this.form), this.parent_div.append(this.context_div)
        },
        createWarningDiv: function() {
            this.warning_div = e("<div />").attr("class", "delete-confirm-warning");
            var t = e("<div />").attr("class", "delete-confirm-warning-message").append(e("<p />").html(this.data.warningMessage + "<br/>" + this.data.detailsMessage)),
                i = e("<div />").attr("class", "delete-confirm-warning-icon delete-notice");
            this.warning_div.append(i).append(t)
        },
        checkTitle: function() {
            return this.text_input.val().substring(0, 5).toLowerCase() == this.data.itemTitle.toString().substring(0, 5).toLowerCase()
        },
        show: function() {
            e("#" + this.$element.data("dialog-id")).modal("show")
        },
        btnToggle: function(t) {
            this.animateToggle(t);
            var i = this.data;
            this.previous_flag != t && (setTimeout(function() {
                e("#" + i.dialogId + "-submit").toggleClass("hide", t)
            }, t ? 100 : 10), this.previous_flag = t)
        },
        animateToggle: function(t) {
            var i = ["btnFadeIn", "btnFadeOut"];
            this.previous_flag != t && e("#" + this.data.dialogId + "-submit").removeClass(i[t ? 0 : 1]).addClass(i[t ? 1 : 0])
        },
        hideSubmitInitial: function() {
            e("#" + this.data.dialogId + "-submit").addClass("hide")
        }
    }, e.fn.confirmdelete = function() {
        return this.each(function() {
            var i = e(this),
                n = i.data("confirmdelete");
            n ? n.show() : i.data("confirmdelete", n = new t(i))
        })
    }, e.fn.confirmdelete.Constructor = t, e(document).on("click.freshdialog.data-api", '[rel="confirmdelete"]', function(t) {
        t.preventDefault();
        var i = e(this);
        i.confirmdelete()
    })
}(window.jQuery);