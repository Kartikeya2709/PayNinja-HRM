@auth
    @php
        $menus = \App\Models\Role::getMenus();
        $role_permission = \App\Models\Role::rolePermissions();
        // dd($menus, $role_permission);
    @endphp
    <div class="main-sidebar sidebar-style-2">
        <aside id="sidebar-wrapper">
            <!-- Add search input -->
            <div class="sidebar-search px-4 pt-5">
                <div class="input-group">
                    <input type="text" class="form-control" id="sidebar-menu-search" placeholder="Search menu...">
                    <div class="input-group-append">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                    </div>
                </div>
            </div>
            <ul class="sidebar-menu">
                <!-- Dashboard -->
                @foreach ($menus as $level1)
                    @if($level1['is_visible'] && (in_array($level1['slug'], $role_permission)))
                        @if(empty($level1['child']))
                            <li class="menu-item {{ Session()->get('active_menu') == $level1['slug'] ? 'active' : '' }}">
                                <a href="{{ url($level1['slug']) }}" class="nav-link">
                                    <i class="menu-icon tf-icons bx {{$level1['icon']}}"></i>
                                    <div data-i18n="{{ $level1['name'] }}">{{ $level1['name'] }}</div>
                                </a>
                            </li>
                        @else
                            @php
                                $hasActiveChild = false;
                                foreach($level1['child'] as $child) {
                                    if(Session()->get('active_menu') == $child['slug']) {
                                        $hasActiveChild = true;
                                        break;
                                    }
                                    // Check if any grandchild is active
                                    if(!empty($child['child'])) {
                                        foreach($child['child'] as $grandChild) {
                                            if(Session()->get('active_menu') == $grandChild['slug']) {
                                                $hasActiveChild = true;
                                                break 2;
                                            }
                                        }
                                    }
                                }
                            @endphp
                            <li class="nav-item dropdown {{ $hasActiveChild ? 'active' : '' }}">
                                <a href="#" class="nav-link has-dropdown">
                                    <i class="menu-icon tf-icons bx {{$level1['icon']}}"></i>
                                    <div data-i18n="{{ $level1['name'] }}">{{ $level1['name'] }}</div>
                                </a>
                                <ul class="dropdown-menu">
                                    @foreach ($level1['child'] as $level2)
                                        @if($level2['is_visible'] && (in_array($level2['slug'], $role_permission)))
                                            @if(empty($level2['child']))
                                                <li class="menu-item {{ Session()->get('active_menu') == $level2['slug'] ? 'active' : '' }}">
                                                    <a href="{{ url($level2['slug']) }}" class="nav-link">
                                                        <i class="menu-icon tf-icons bx {{$level2['icon']}}"></i>
                                                        <div data-i18n="{{ $level2['name'] }}">{{ $level2['name'] }}</div>
                                                    </a>
                                                </li>
                                            @else
                                                @php
                                                    $hasActiveGrandChild = false;
                                                    foreach($level2['child'] as $grandChild) {
                                                        if(Session()->get('active_menu') == $grandChild['slug']) {
                                                            $hasActiveGrandChild = true;
                                                            break;
                                                        }
                                                    }
                                                @endphp
                                                <li class="nav-item dropdown {{ $hasActiveGrandChild ? 'active' : '' }}">
                                                    <a href="#" class="nav-link has-dropdown">
                                                        <div class="text-truncate" data-i18n="{{ $level2['name']}}">{{ $level2['name']}}</div>
                                                    </a>
                                                    <ul class="dropdown-menu">
                                                        @foreach ($level2['child'] as $level3)
                                                            @if($level3['is_visible'] && (in_array($level3['slug'], $role_permission)))
                                                                <li class="menu-item {{ Session()->get('active_menu') == $level3['slug'] ? 'active' : '' }}">
                                                                    <a href="{{ url($level3['slug']) }}" class="nav-link">
                                                                        <i class="menu-icon tf-icons bx {{$level3['icon']}}"></i>
                                                                        <div class="text-truncate" data-i18n="Role">{{ $level3['name'] }}</div>
                                                                    </a>
                                                                </li>
                                                            @endif
                                                        @endforeach
                                                    </ul>
                                                </li>
                                            @endif
                                        @endif
                                    @endforeach
                                </ul>
                            </li>
                        @endif
                    @endif
                @endforeach
            </ul>
        </aside>
    </div>
@endauth
