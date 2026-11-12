@php
    // ---- Computed once (the original repeated this expression twice) ----
    $authUser      = Auth::user();
    $defaultAvatar = asset('images/profile_pic/' . ($authUser->gender === 'male' ? 'male.png' : 'female.png'));
    $avatar        = $authUser->profile_picture ? asset('images/profile_pic/' . $authUser->profile_picture) : $defaultAvatar;
    $shortName     = \Illuminate\Support\Str::words($authUser->name, 2, '');

    // TODO: put the real profile route here (e.g. route('profile')); '#' keeps the link inert for now
    $profileUrl = '#';
@endphp

<style>
    /* ---------- Quick page search ---------- */
    .main-header .qs-wrap { position: relative; }
    .main-header .qs-results,
    .main-header .qs-panel .qs-results-static {
        background: #fff; border: 1px solid #e6e8ef; border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,.12); overflow: hidden; z-index: 1060;
    }
    .main-header .qs-results { position: absolute; top: calc(100% + 6px); inset-inline-start: 0; width: 100%; min-width: 280px; display: none; }
    .main-header .qs-results.open { display: block; }
    .main-header .qs-item { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 10px 14px; color: #1f2430; text-decoration: none; border-bottom: 1px solid #f1f2f6; }
    .main-header .qs-item:last-child { border-bottom: 0; }
    .main-header .qs-item small { color: #9aa0ae; white-space: nowrap; }
    .main-header .qs-item:hover, .main-header .qs-item.active { background: #f0f5ff; color: #0162e8; text-decoration: none; }
    .main-header .qs-empty { padding: 14px; text-align: center; color: #9aa0ae; font-size: 13px; }
    .main-header .qs-kbd { position: absolute; inset-inline-end: 52px; top: 50%; transform: translateY(-50%); font-size: 11px; color: #9aa0ae; border: 1px solid #e0e3ec; border-radius: 5px; padding: 0 6px; pointer-events: none; }
    .main-header .qs-wrap input:focus ~ .qs-kbd { display: none; }

    /* mobile search panel */
    .main-header .qs-panel { position: fixed; top: 64px; left: 0; right: 0; background: #fff; padding: 12px 14px; box-shadow: 0 8px 20px rgba(0,0,0,.12); z-index: 1055; display: none; }
    .main-header .qs-panel.open { display: block; }
    .main-header .qs-panel .qs-results-static { margin-top: 8px; display: none; }
    .main-header .qs-panel .qs-results-static.open { display: block; }

    /* ---------- Header extras ---------- */
    .main-header .header-date { align-items: center; font-size: 13px; color: #7b8190; margin: 0 10px; white-space: nowrap; }
    .main-header .new-sale-btn { border-radius: 8px; font-weight: 600; white-space: nowrap; }
    .main-header .header-icon-btn { display: inline-flex; align-items: center; justify-content: center; }
    .main-header .header-user-name { font-weight: 600; font-size: 13px; margin: 0 8px; align-self: center; }
    .main-header .main-header-profile h6, .main-header .main-header-profile span { max-width: 190px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: block; }
    .main-header .dropdown-item { display: flex; align-items: center; gap: 8px; }
    .main-header .dropdown-item i { font-size: 18px; }
    .main-header .dropdown-item.logout-item { color: #ec2e2e; }
    .main-header .dropdown-item.logout-item:hover { background: #fde8e8; }

    .dark-theme .main-header .qs-results,
    .dark-theme .main-header .qs-panel,
    .dark-theme .main-header .qs-panel .qs-results-static { background: #1e2230; border-color: #2c3144; }
    .dark-theme .main-header .qs-item { color: #e5e7ef; border-color: #2c3144; }
    .dark-theme .main-header .qs-item:hover, .dark-theme .main-header .qs-item.active { background: #272c40; color: #6ea8ff; }
</style>

<!-- main-header opened -->
<div class="main-header sticky side-header nav nav-item">
    <div class="container-fluid">
        <div class="main-header-left">
            <div class="responsive-logo">
                <a href="{{ route('home') }}"><img src="{{ URL::asset('assets/img/brand/logo.png') }}" class="logo-1" alt="معرض أيه"></a>
                <a href="{{ route('home') }}"><img src="{{ URL::asset('assets/img/brand/logo-white.png') }}" class="dark-logo-1" alt="معرض أيه"></a>
                <a href="{{ route('home') }}"><img src="{{ URL::asset('assets/img/brand/favicon.png') }}" class="logo-2" alt="معرض أيه"></a>
                <a href="{{ route('home') }}"><img src="{{ URL::asset('assets/img/brand/favicon.png') }}" class="dark-logo-2" alt="معرض أيه"></a>
            </div>

            <div class="app-sidebar__toggle" data-toggle="sidebar">
                <a class="open-toggle" href="#" aria-label="فتح القائمة"><i class="header-icon fe fe-align-left"></i></a>
                <a class="close-toggle" href="#" aria-label="إغلاق القائمة"><i class="header-icons fe fe-x"></i></a>
            </div>

            {{-- Desktop quick search (jump to any page) --}}
            <div class="main-header-center mr-3 d-sm-none d-md-none d-lg-block">
                <div class="qs-wrap" id="qsDesktop">
                    <input class="form-control qs-input" type="search" autocomplete="off"
                           placeholder="ابحث عن صفحة... (مثال: عملاء، فواتير)" aria-label="بحث سريع عن صفحة"
                           role="combobox" aria-expanded="false">
                    <button class="btn" type="button" tabindex="-1" aria-hidden="true"><i class="fas fa-search d-none d-md-block"></i></button>
                    <span class="qs-kbd d-none d-xl-inline">/</span>
                    <div class="qs-results" role="listbox"></div>
                </div>
            </div>
        </div>

        <div class="main-header-right">
            <div class="nav nav-item navbar-nav-right ml-auto align-items-center">

                {{-- Quick action: new sale (hidden on the sales page itself) --}}
                @unless (request()->routeIs('sales_invoice'))
                    <a href="{{ route('sales_invoice') }}" class="btn btn-primary btn-sm new-sale-btn d-none d-md-inline-flex align-items-center mx-2">
                        <i class="fas fa-plus ml-1"></i> بيع جديد
                    </a>
                @endunless

                {{-- Today's date --}}
                <span class="header-date d-none d-xl-inline-flex">
                    <i class="far fa-calendar-alt ml-1"></i><span id="headerDate"></span>
                </span>

                {{-- Mobile search toggle --}}
                <div class="nav-item d-lg-none">
                    <a class="nav-link header-icon-btn" href="javascript:void(0);" id="qsMobileToggle" aria-label="بحث" role="button">
                        <svg xmlns="http://www.w3.org/2000/svg" class="header-icon-svgs" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    </a>
                </div>

                {{-- Fullscreen (handled by the theme) --}}
                <div class="nav-item full-screen fullscreen-button">
                    <a class="new nav-link full-screen-link" href="#" title="ملء الشاشة" aria-label="ملء الشاشة">
                        <svg xmlns="http://www.w3.org/2000/svg" class="header-icon-svgs" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path></svg>
                    </a>
                </div>

                {{-- Profile --}}
                <div class="dropdown main-profile-menu nav nav-item nav-link">
                    <a class="profile-user d-flex" href="javascript:void(0);" aria-haspopup="true" aria-label="قائمة المستخدم">
                        <img alt="{{ $authUser->name }}" class="avatar brround" src="{{ $avatar }}"
                             onerror="this.onerror=null;this.src='{{ $defaultAvatar }}'">
                        <span class="header-user-name d-none d-md-inline">{{ $shortName }}</span>
                    </a>
                    <div class="dropdown-menu">
                        <div class="main-header-profile bg-primary p-3">
                            <div class="d-flex wd-100p">
                                <div class="main-img-user">
                                    <img alt="{{ $authUser->name }}" class="avatar avatar-xl brround" src="{{ $avatar }}"
                                         onerror="this.onerror=null;this.src='{{ $defaultAvatar }}'">
                                </div>
                                <div class="mr-3 my-auto">
                                    <h6 title="{{ $authUser->name }}">{{ $authUser->name }}</h6>
                                    <span title="{{ $authUser->email }}">{{ $authUser->email }}</span>
                                </div>
                            </div>
                        </div>

                        <a class="dropdown-item" href="{{ $profileUrl }}"><i class="bx bx-user-circle"></i> الصفحة الشخصية</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item logout-item" href="{{ route('logout') }}"
                           onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                            <i class="bx bx-log-out"></i> تسجيل خروج
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Mobile search panel --}}
    <div class="qs-panel" id="qsMobilePanel">
        <div class="qs-wrap" id="qsMobile">
            <input class="form-control qs-input" type="search" autocomplete="off" placeholder="ابحث عن صفحة..." aria-label="بحث سريع عن صفحة">
            <div class="qs-results-static" role="listbox"></div>
        </div>
    </div>
</div>
<!-- /main-header -->

<script>
    document.addEventListener('DOMContentLoaded', function () {

        /* ---------- Today's date ---------- */
        var dateEl = document.getElementById('headerDate');
        if (dateEl) {
            try {
                dateEl.textContent = new Date().toLocaleDateString('ar-EG', { weekday: 'long', day: 'numeric', month: 'long' });
            } catch (e) { dateEl.parentNode.style.display = 'none'; }
        }

        /* ---------- Quick page search ----------
         * Builds its index from the sidebar links, so it always matches the real menu
         * (and needs no backend). Arabic-friendly: ignores tashkeel and أ/إ/آ, ة/ه, ى/ي. */
        function norm(s) {
            return (s || '').toLowerCase()
                .replace(/[\u064B-\u0652\u0640]/g, '')
                .replace(/[أإآ]/g, 'ا').replace(/ة/g, 'ه').replace(/ى/g, 'ي').trim();
        }

        var pages = null;
        function getPages() {
            if (pages) return pages;
            pages = [];
            var seen = {};
            Array.prototype.forEach.call(document.querySelectorAll('.app-sidebar a[href]'), function (a) {
                var href = a.getAttribute('href');
                if (!href || href === '#' || href.indexOf('javascript') === 0) return;
                var labelEl = a.querySelector('.side-menu__label');
                var label = (labelEl ? labelEl.textContent : a.textContent).trim();
                if (!label || seen[a.href]) return;
                seen[a.href] = true;

                var group = '';
                var li = a.closest('li.slide');
                if (li && a.classList.contains('slide-item')) {
                    var g = li.querySelector('.side-menu__label');
                    group = g ? g.textContent.trim() : '';
                }
                pages.push({ label: label, group: group, href: a.href, key: norm(label + ' ' + group) });
            });
            return pages;
        }

        function bindSearch(input, box) {
            var active = -1, current = [];

            function close() { box.classList.remove('open'); input.setAttribute('aria-expanded', 'false'); active = -1; }

            function setActive(i) {
                var items = box.querySelectorAll('.qs-item');
                Array.prototype.forEach.call(items, function (el, idx) { el.classList.toggle('active', idx === i); });
                active = i;
                if (items[i] && items[i].scrollIntoView) items[i].scrollIntoView({ block: 'nearest' });
            }

            function render() {
                var q = norm(input.value);
                box.innerHTML = '';
                if (!q) { close(); return; }

                current = getPages().filter(function (p) { return p.key.indexOf(q) !== -1; }).slice(0, 8);

                if (!current.length) {
                    var none = document.createElement('div');
                    none.className = 'qs-empty';
                    none.textContent = 'لا توجد صفحات مطابقة';
                    box.appendChild(none);
                } else {
                    current.forEach(function (p) {
                        var a = document.createElement('a');
                        a.className = 'qs-item';
                        a.href = p.href;
                        a.setAttribute('role', 'option');
                        var t = document.createElement('span'); t.textContent = p.label;
                        a.appendChild(t);
                        if (p.group) { var s = document.createElement('small'); s.textContent = p.group; a.appendChild(s); }
                        box.appendChild(a);
                    });
                }
                box.classList.add('open');
                input.setAttribute('aria-expanded', 'true');
                setActive(current.length ? 0 : -1);
            }

            input.addEventListener('input', render);
            input.addEventListener('focus', render);
            input.addEventListener('keydown', function (e) {
                if (e.key === 'ArrowDown') { e.preventDefault(); if (current.length) setActive((active + 1) % current.length); }
                else if (e.key === 'ArrowUp') { e.preventDefault(); if (current.length) setActive((active - 1 + current.length) % current.length); }
                else if (e.key === 'Enter') { e.preventDefault(); if (current[active]) window.location.href = current[active].href; }
                else if (e.key === 'Escape') { input.value = ''; box.innerHTML = ''; close(); input.blur(); }
            });
            document.addEventListener('click', function (e) {
                if (!box.parentNode.contains(e.target)) close();
            });
        }

        var dInput = document.querySelector('#qsDesktop .qs-input');
        var dBox   = document.querySelector('#qsDesktop .qs-results');
        var mInput = document.querySelector('#qsMobile .qs-input');
        var mBox   = document.querySelector('#qsMobile .qs-results-static');
        if (dInput && dBox) bindSearch(dInput, dBox);
        if (mInput && mBox) bindSearch(mInput, mBox);

        /* ---------- Mobile search panel ---------- */
        var panel  = document.getElementById('qsMobilePanel');
        var toggle = document.getElementById('qsMobileToggle');
        if (panel && toggle) {
            toggle.addEventListener('click', function (e) {
                e.preventDefault();
                panel.classList.toggle('open');
                if (panel.classList.contains('open') && mInput) mInput.focus();
            });
        }

        /* ---------- Keyboard shortcut: "/" or Ctrl/Cmd+K ---------- */
        document.addEventListener('keydown', function (e) {
            var tag = (e.target.tagName || '').toLowerCase();
            var typing = tag === 'input' || tag === 'textarea' || tag === 'select' || e.target.isContentEditable;
            var wantsSearch = (e.key === '/' && !typing) || ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k');
            if (!wantsSearch) return;
            e.preventDefault();
            if (dInput && dInput.offsetParent !== null) dInput.focus();
            else if (panel && mInput) { panel.classList.add('open'); mInput.focus(); }
        });
    });
</script>
