(function($) {
    var doneVal = '';

    var count = 0;

    var length = 0;
    $.fn.code = function(options) {
        var that = this;



        if (!this.length) {
            return this;
        }

        var opts = $.extend(true, {}, $.fn.code.defaults, options);

        length = opts.length;

        var temp = '';

        for (var i = 0; i < opts.length; i++) {
            temp += "<div class='code-input-item" + (opts.skin != '' ? ' ' + opts.skin : '') + "'><input type='text'" + (i != 0 ? " readOnly" : '') + " name='" + ('code' + (i + 1)) + "'></div>";
        }

        // cha ru jiedian
        $(this).append(temp);

        // 禁止粘贴
        $(this).on("paste", ".code-input-item input[type='text']", function() {
                return false;
            })
            // 按下
        $(this).on("input", ".code-input-item input[type='text']", function(e) {
            var val = $(this).val();
            var reg = /^[0-9a-zA-Z]*$/g;
            if (isNaN(val) && reg.test(val) == false) {
                $(this).val('');
                return false;
            }
            if (val.length == 1) {
                $.fn.jump($(this), 'next');
            } else {
                $(this).val('');
            }
            if (val.length > 1) {
                $(this).val(val.substr(1, val.length));
                $(this).parent().next().children("input").removeAttr('readOnly').focus();
            }
            opts.before && opts.before($(this), val);
            var code = $.fn.getCode(that);
            if (code) {
                // 当前输入框一定是最后一个才算完成
                if (($(this).parent().index() + 1) == length) {
                    opts.done && opts.done(that, code);
                }
                opts.success && opts.success(that, code);
            }
        })

        $(this).on("keyup", ".code-input-item input[type='text']", function(e) {
            var val = $(this).val();
            if (val == '' && e.keyCode == 8) {
                opts.backspace && opts.backspace($(this));
                if (count != 0) {
                    $.fn.jump($(this), 'prev');
                }
            }
        })
        return this;
    };

    $.fn.jump = function(dom, type) {
        if (type == 'next') {
            if (count < length) {
                count++;
                dom.parent().next().children("input").removeAttr('readOnly').focus();
            }
        } else {
            if (count != 0) {
                count--;
                dom.parent().prev().children("input").removeAttr('readOnly').focus();
            }
        }
    }

    $.fn.getCode = function(t) {
        var code = '';
        var seftCount = 0;
        if (t == undefined) {
            t = this;
        }
        $(t).find(".code-input-item").each(function() {
            var val = $(this).children("input[type='text']").val();
            if (val == '') {
                $(this).addClass('error')
                return false;
            } else {
                $(this).removeClass('error')
                code += val;
                seftCount++;
            }
        })
        if (seftCount == length) {
            console.log("current function getCode()，value：" + code);
            return code;
        } else {
            return '';
        }
    };

    $.fn.reset = function() {
        var that = this;
        $(that).find("input").val('');
        $.fn.lock(that);
        count = 0;
        return true;
    };

    $.fn.lock = function(t) {
        if (t == undefined) {
            t = this;
        }
        $(t).find("input").attr('readOnly', true);
        $(t).find("input").eq(0).removeAttr('readOnly');
        return true;
    }

    // default options
    $.fn.code.defaults = {
        length: 6,
        skin: '',
        keyup: function(t, v) {},
        done: function(parent, val) {},
        success: function(parent, val) {},
        before: function(t, val) {},
        backspace: function(t) {}
    };

})(jQuery);