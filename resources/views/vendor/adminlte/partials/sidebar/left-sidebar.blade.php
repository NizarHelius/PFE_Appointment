<aside class="main-sidebar {{ config('adminlte.classes_sidebar', 'sidebar-dark-primary elevation-4') }}">

    {{-- Sidebar brand logo --}}
    @if(config('adminlte.logo_img_xl'))
    @include('adminlte::partials.common.brand-logo-xl')
    @else
    @include('adminlte::partials.common.brand-logo-xs')
    @endif

    {{-- Sidebar menu --}}
    <div class="sidebar" style="overflow: hidden;">
        <nav class="pt-2">
            <ul class="nav nav-pills nav-sidebar flex-column {{ config('adminlte.classes_sidebar_nav', '') }}"
                data-widget="treeview" role="menu"
                @if(config('adminlte.sidebar_nav_animation_speed') !=300)
                data-animation-speed="{{ config('adminlte.sidebar_nav_animation_speed') }}"
                @endif
                @if(!config('adminlte.sidebar_nav_accordion'))
                data-accordion="false"
                @endif>
                {{-- Configured sidebar links --}}
                @each('adminlte::partials.sidebar.menu-item', $adminlte->menu('sidebar'), 'item')
            </ul>
        </nav>
    </div>

</aside>

<style>
    /* Improved Sidebar Styling */
    .main-sidebar {
        transition: all 0.3s ease;
    }

    .sidebar {
        scrollbar-width: thin;
    }

    .nav-sidebar>.nav-item {
        margin-bottom: 2px;
    }

    .nav-sidebar>.nav-item>.nav-link {
        border-radius: 0 20px 20px 0;
        margin-right: 10px;
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
    }

    .nav-sidebar>.nav-item>.nav-link:hover:not(.active) {
        background-color: rgba(255, 255, 255, 0.1);
        transform: translateX(4px);
    }

    .nav-sidebar>.nav-item>.nav-link.active {
        font-weight: 600;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.2);
    }

    .nav-sidebar .nav-treeview {
        padding-left: 10px;
    }

    .nav-sidebar .nav-treeview .nav-link {
        border-radius: 20px;
        margin-right: 15px;
    }

    .nav-sidebar .nav-treeview .nav-link:hover {
        background-color: rgba(255, 255, 255, 0.05);
    }

    .nav-sidebar .menu-open>.nav-link {
        background-color: rgba(0, 0, 0, 0.2);
    }
</style>