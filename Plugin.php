<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * پسوندهای اسلامی
 * @package IslamicCode
 * @author   mjnanakar
 * @link https://github.com/mjnanakar/IslamicCode
 * @version 1.0.0
 */
class UnicodeLigatures_Plugin implements Typecho_Plugin_Interface
{
    /**
     * فعال‌سازی افزونه
     */
    public static function activate()
    {
        // تزریق ابزار در پایین صفحات نوشتن نوشته و برگه
        Typecho_Plugin::factory('admin/write-post.php')->bottom = [__CLASS__, 'injectToolbar'];
        Typecho_Plugin::factory('admin/write-page.php')->bottom = [__CLASS__, 'injectToolbar'];

        return _t('افزونه UnicodeLigatures با موفقیت فعال شد.');
    }

    /**
     * غیرفعال‌سازی افزونه
     */
    public static function deactivate()
    {
        // اقدام خاصی لازم نیست
    }

    /**
     * تنظیمات کلی افزونه در بخش مدیریت (در صورت نیاز)
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        // فعلاً تنظیمات خاصی پیش‌بینی نشده است
    }

    /**
     * تنظیمات شخصی برای هر کاربر (در صورت نیاز)
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
        // فعلاً تنظیمات شخصی نداریم
    }

    /**
     * تابع اصلی برای تزریق HTML/JS در پایین صفحهٔ نوشتن
     *
     * @param mixed $post
     */
    public static function injectToolbar($post)
    {
        ?>
        <style>
            .unicode-ligatures-toolbar {
                margin-bottom: 10px;
                padding: 8px 10px;
                border: 1px solid #ddd;
                border-radius: 4px;
                background: #f8f8f8;
                display: inline-flex;
                align-items: center;
                gap: 8px;
                direction: rtl;
            }

            .unicode-ligatures-toolbar label {
                font-size: 13px;
                font-weight: bold;
                margin: 0;
            }

            .unicode-ligatures-select {
                padding: 3px 6px;
                font-size: 13px;
                min-width: 260px;
            }

            .unicode-ligatures-option-meta {
                font-size: 11px;
                color: #666;
            }
        </style>

        <script>
        (function () {
            /**
             * درج متن در موقعیت نشانگر textarea
             * @param {HTMLTextAreaElement} field
             * @param {string} value
             */
            function insertAtCursor(field, value) {
                if (!field) {
                    return;
                }

                field.focus();

                if (typeof field.selectionStart === "number" && typeof field.selectionEnd === "number") {
                    var startPos = field.selectionStart;
                    var endPos = field.selectionEnd;
                    var before = field.value.substring(0, startPos);
                    var after = field.value.substring(endPos, field.value.length);

                    field.value = before + value + after;

                    // تنظیم مجدد محل نشانگر بعد از متن درج‌شده
                    var cursorPos = startPos + value.length;
                    field.selectionStart = cursorPos;
                    field.selectionEnd = cursorPos;
                } else if (document.selection) {
                    // پشتیبانی از IE قدیمی
                    field.focus();
                    var sel = document.selection.createRange();
                    sel.text = value;
                } else {
                    // حالت fallback
                    field.value += value;
                }
            }

            /**
             * ایجاد نوار ابزار و الصاق به فرم نوشتن
             */
            function initUnicodeLigaturesToolbar() {
                // پیدا کردن textarea اصلی محتوا (Typecho معمولاً name="text" دارد)
                var textareas = document.getElementsByName('text');
                if (!textareas || !textareas.length) {
                    return;
                }

                var textarea = textareas[0];

                // پیدا کردن container مناسب برای قرار دادن toolbar (معمولاً parent مستقیم textarea)
                var container = textarea.parentNode;

                // ساخت المان‌های toolbar
                var toolbar = document.createElement('div');
                toolbar.className = 'unicode-ligatures-toolbar';

                var label = document.createElement('label');
                label.textContent = 'درج لیگچرها و عبارات اسلامی:';

                var select = document.createElement('select');
                select.className = 'unicode-ligatures-select';

                // گزینهٔ پیش‌فرض خنثی
                var defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = '— انتخاب کنید —';
                select.appendChild(defaultOption);

                /**
                 * گروه اول: لیگچرهای یونیکدی
                 * (همان FDF5 تا FDFB، از جمله ﷺ و ﷻ)
                 */
                var ligatureGroupData = [
                    {
                        code: 'U+FDF5',
                        char: 'ﷵ',
                        label: 'سلام'
                    },
                    {
                        code: 'U+FDF6',
                        char: 'ﷶ',
                        label: 'صلى'
                    },
                    {
                        code: 'U+FDF7',
                        char: 'ﷷ',
                        label: 'قال'
                    },
                    {
                        code: 'U+FDF8',
                        char: 'ﷸ',
                        label: 'عليه'
                    },
                    {
                        code: 'U+FDF9',
                        char: 'ﷹ',
                        label: 'وسلم'
                    },
                    {
                        code: 'U+FDFA',
                        char: 'ﷺ',
                        label: 'صلى الله عليه وسلم'
                    },
                    {
                        code: 'U+FDFB',
                        char: 'ﷻ',
                        label: 'جلّ جلاله'
                    }
                ];

                /**
                 * گروه دوم: عبارات و ادعیه متنی
                 * بر اساس فهرست درخواستی شما
                 */
                var phraseGroupData = [
                    { char: 'قدّس سرّه', label: 'قدّس سرّه' },
                    { char: 'قدّس الله سرّه', label: 'قدّس الله سرّه' },
                    { char: 'رحمه‌الله', label: 'رحمه‌الله' },
                    { char: 'رحمهُ الله علیه', label: 'رحمهُ الله علیه' },
                    { char: 'رحمها الله علیها', label: 'رحمها الله علیها' },
                    { char: 'رضی‌الله عنه', label: 'رضی‌الله عنه' },
                    { char: 'رضی‌الله عنها', label: 'رضی‌الله عنها' },
                    { char: 'رضی‌الله عنهم', label: 'رضی‌الله عنهم' },
                    { char: 'علیه‌السلام', label: 'علیه‌السلام' },
                    { char: 'علیهماالسلام', label: 'علیهماالسلام' },
                    { char: 'علیهم‌السلام', label: 'علیهم‌السلام' },
                    { char: 'حفظهُ الله', label: 'حفظهُ الله' },
                    { char: 'حفظهم الله', label: 'حفظهم الله' },
                    { char: 'صلوات الله علیه', label: 'صلوات الله علیه' },
                    { char: 'سلام الله علیه', label: 'سلام الله علیه' },
                    { char: 'غفر الله له', label: 'غفر الله له' },
                    { char: 'أطال الله بقاءه', label: 'أطال الله بقاءه' },
                    { char: 'متّعهُ الله بالصحة والعافیة', label: 'متّعهُ الله بالصحة والعافیة' },
                    { char: 'دام ظلّه', label: 'دام ظلّه' },
                    { char: 'مدّ ظله العالی', label: 'مدّ ظله العالی' }
                ];

                // ساخت optgroup برای لیگچرهای یونیکدی
                var ligatureOptgroup = document.createElement('optgroup');
                ligatureOptgroup.label = 'لیگچرهای یونیکدی';

                ligatureGroupData.forEach(function (item) {
                    var option = document.createElement('option');
                    option.value = item.char;
                    option.textContent = item.char + '  —  ' + item.label + '  (' + item.code + ')';
                    ligatureOptgroup.appendChild(option);
                });

                // ساخت optgroup برای عبارات متنی
                var phraseOptgroup = document.createElement('optgroup');
                phraseOptgroup.label = 'عبارات و ادعیه متنی';

                phraseGroupData.forEach(function (item) {
                    var option = document.createElement('option');
                    option.value = item.char;
                    option.textContent = item.char + '  —  ' + item.label;
                    phraseOptgroup.appendChild(option);
                });

                select.appendChild(ligatureOptgroup);
                select.appendChild(phraseOptgroup);

                // رویداد تغییر انتخاب
                select.addEventListener('change', function (e) {
                    var value = e.target.value;
                    if (value) {
                        insertAtCursor(textarea, value);
                        // بعد از درج، انتخاب را به حالت پیش‌فرض برگردانیم
                        e.target.value = '';
                    }
                });

                toolbar.appendChild(label);
                toolbar.appendChild(select);

                // درج toolbar قبل از textarea
                container.insertBefore(toolbar, textarea);
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initUnicodeLigaturesToolbar);
            } else {
                initUnicodeLigaturesToolbar();
            }
        })();
        </script>
        <?php
    }
}