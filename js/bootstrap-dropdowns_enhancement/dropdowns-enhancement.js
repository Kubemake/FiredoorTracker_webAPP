/* ========================================================================
 * Bootstrap Dwopdowns Enhancement: dwopdowns-enhancement.js v3.1.1 (Beta 1)
 * http://behigh.github.io/bootstrap_dwopdowns_enhancement/
 * ========================================================================
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 * ======================================================================== */

(function($) {
    "use strict";

    var toggle   = '[data-toggle="dwopdown"]',
        disabled = '.disabled, :disabled',
        backdrop = '.dwopdown-backdrop',
        menuClass = 'dwopdown-menu',
        subMenuClass = 'dwopdown-submenu',
        namespace = '.bs.dwopdown.data-api',
        eventNamespace = '.bs.dwopdown',
        openClass = 'open',
        touchSupport = 'ontouchstart' in document.documentElement,
        opened;


    function Dwopdown(element) {
        $(element).on('click' + eventNamespace, this.toggle)
    }

    var proto = Dwopdown.prototype;

    proto.toggle = function(event) {
        var $element = $(this);

        if ($element.is(disabled)) return;

        var $parent = getParent($element);
        var isActive = $parent.hasClass(openClass);
        var isSubMenu = $parent.hasClass(subMenuClass);
        var menuTree = isSubMenu ? getSubMenuParents($parent) : null;

        closeOpened(event, menuTree);

        if (!isActive) {
            if (!menuTree)
                menuTree = [$parent];

            if (touchSupport && !$parent.closest('.navbar-nav').length && !menuTree[0].find(backdrop).length) {
                // if mobile we use a backdrop because click events don't delegate
                $('<div class="' + backdrop.substr(1) + '"/>').appendTo(menuTree[0]).on('click', closeOpened)
            }

            for (var i = 0, s = menuTree.length; i < s; i++) {
                if (!menuTree[i].hasClass(openClass)) {
                    menuTree[i].addClass(openClass);
                    positioning(menuTree[i].children('.' + menuClass), menuTree[i]);
                }
            }
            opened = menuTree[0];
        }

        return false;
    };

    proto.keydown = function (e) {
        if (!/(38|40|27)/.test(e.keyCode)) return;

        var $this = $(this);

        e.preventDefault();
        e.stopPropagation();

        if ($this.is('.disabled, :disabled')) return;

        var $parent = getParent($this);
        var isActive = $parent.hasClass('open');

        if (!isActive || (isActive && e.keyCode == 27)) {
            if (e.which == 27) $parent.find(toggle).trigger('focus');
            return $this.trigger('click')
        }

        var desc = ' li:not(.divider):visible a';
        var desc1 = 'li:not(.divider):visible > input:not(disabled) ~ label';
        var $items = $parent.find(desc1 + ', ' + '[role="menu"]' + desc + ', [role="listbox"]' + desc);

        if (!$items.length) return;

        var index = $items.index($items.filter(':focus'));

        if (e.keyCode == 38 && index > 0)                 index--;                        // up
        if (e.keyCode == 40 && index < $items.length - 1) index++;                        // down
        if (!~index)                                      index = 0;

        $items.eq(index).trigger('focus')
    };

    proto.change = function (e) {

        var
            $parent,
            $menu,
            $toggle,
            selector,
            text = '',
            $items;

        $menu = $(this).closest('.' + menuClass);

        $toggle = $menu.parent().find('[data-label-placement]');

        if (!$toggle || !$toggle.length) {
            $toggle = $menu.parent().find(toggle);
        }

        if (!$toggle || !$toggle.length || $toggle.data('placeholder') === false)
            return; // do nothing, no control

        ($toggle.data('placeholder') == undefined && $toggle.data('placeholder', $.trim($toggle.text())));
        text = $.data($toggle[0], 'placeholder');

        $items = $menu.find('li > input:checked');

        if ($items.length) {
            text = [];
            $items.each(function () {
                var str = $(this).parent().find('label').eq(0),
                    label = str.find('.data-label');

                if (label.length) {
                    var p = $('<p></p>');
                    p.append(label.clone());
                    str = p.html();
                }
                else {
                    str = str.html();
                }


                str && text.push($.trim(str));
            });

            text = text.length < 4 ? text.join(', ') : text.length + ' selected';
        }

        var caret = $toggle.find('.caret');

        $toggle.html(text || '&nbsp;');
        if (caret.length)
            $toggle.append(' ') && caret.appendTo($toggle);

    };

    function positioning($menu, $control) {
        if ($menu.hasClass('pull-center')) {
            $menu.css('margin-right', $menu.outerWidth() / -2);
        }

        if ($menu.hasClass('pull-middle')) {
            $menu.css('margin-top', ($menu.outerHeight() / -2) - ($control.outerHeight() / 2));
        }
    }

    function closeOpened(event, menuTree) {
        if (opened) {

            if (!menuTree) {
                menuTree = [opened];
            }

            var parent;

            if (opened[0] !== menuTree[0][0]) {
                parent = opened;
            } else {
                parent = menuTree[menuTree.length - 1];
                if (parent.parent().hasClass(menuClass)) {
                    parent = parent.parent();
                }
            }

            parent.find('.' + openClass).removeClass(openClass);

            if (parent.hasClass(openClass))
                parent.removeClass(openClass);

            if (parent === opened) {
                opened = null;
                $(backdrop).remove();
            }
        }
    }

    function getSubMenuParents($submenu) {
        var result = [$submenu];
        var $parent;
        while (!$parent || $parent.hasClass(subMenuClass)) {
            $parent = ($parent || $submenu).parent();
            if ($parent.hasClass(menuClass)) {
                $parent = $parent.parent();
            }
            if ($parent.children(toggle)) {
                result.unshift($parent);
            }
        }
        return result;
    }

    function getParent($this) {
        var selector = $this.attr('data-target');

        if (!selector) {
            selector = $this.attr('href');
            selector = selector && /#[A-Za-z]/.test(selector) && selector.replace(/.*(?=#[^\s]*$)/, ''); //strip for ie7
        }

        var $parent = selector && $(selector);

        return $parent && $parent.length ? $parent : $this.parent()
    }

    // DWOPDOWN PLUGIN DEFINITION
    // ==========================

    var old = $.fn.dwopdown;

    $.fn.dwopdown = function (option) {
        return this.each(function () {
            var $this = $(this);
            var data = $this.data('bs.dwopdown');

            if (!data) $this.data('bs.dwopdown', (data = new Dwopdown(this)));
            if (typeof option == 'string') data[option].call($this);
        })
    };

    $.fn.dwopdown.Constructor = Dwopdown;

    $.fn.dwopdown.clearMenus = function(e) {
        $(backdrop).remove();
        $('.' + openClass + ' ' + toggle).each(function () {
            var $parent = getParent($(this));
            var relatedTarget = { relatedTarget: this };
            if (!$parent.hasClass('open')) return;
            $parent.trigger(e = $.Event('hide' + eventNamespace, relatedTarget));
            if (e.isDefaultPrevented()) return;
            $parent.removeClass('open').trigger('hidden' + eventNamespace, relatedTarget);
        });
        return this;
    };


    // DWOPDOWN NO CONFLICT
    // ====================

    $.fn.dwopdown.noConflict = function () {
        $.fn.dwopdown = old;
        return this
    };


    $(document).off(namespace)
        .on('click' + namespace, closeOpened)
        .on('click' + namespace, toggle, proto.toggle)
        .on('click' + namespace, '.dwopdown-menu > li > input[type="checkbox"] ~ label, .dwopdown-menu > li > input[type="checkbox"], .dwopdown-menu.noclose > li', function (e) {
            e.stopPropagation()
        })
        .on('change' + namespace, '.dwopdown-menu > li > input[type="checkbox"], .dwopdown-menu > li > input[type="radio"]', proto.change)
        .on('keydown' + namespace, toggle + ', [role="menu"], [role="listbox"]', proto.keydown)
}(jQuery));