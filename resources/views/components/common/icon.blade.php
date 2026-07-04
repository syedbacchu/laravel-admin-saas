@props(['name'])

@switch($name)
    @case('dashboard')
        <svg class="group-hover:!text-primary" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="3" y="3" width="8" height="8" rx="2" fill="#8B5CF6"/>
            <rect x="13" y="3" width="8" height="8" rx="2" fill="#8B5CF6" opacity="0.6"/>
            <rect x="3" y="13" width="8" height="8" rx="2" fill="#8B5CF6" opacity="0.6"/>
            <rect x="13" y="13" width="8" height="8" rx="2" fill="#8B5CF6"/>
        </svg>
        @break

    @case('users')
        <svg class="group-hover:!text-primary" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 12C14.7614 12 17 9.76142 17 7C17 4.23858 14.7614 2 12 2C9.23858 2 7 4.23858 7 7C7 9.76142 9.23858 12 12 12Z" fill="#8B5CF6"/>
            <path d="M12 14C7.58172 14 4 17.5817 4 22H20C20 17.5817 16.4183 14 12 14Z" fill="#8B5CF6" opacity="0.7"/>
            <path d="M17 12C19.7614 12 22 9.76142 22 7C22 4.23858 19.7614 2 17 2C16.0432 2 15.1486 2.27614 14.3829 2.75134C15.3619 3.91381 16 5.38079 16 7C16 8.61921 15.3619 10.0862 14.3829 11.2487C15.1486 11.7239 16.0432 12 17 12Z" fill="#8B5CF6" opacity="0.4"/>
        </svg>
        @break

    @case('user')
        <svg class="group-hover:!text-primary" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="12" cy="8" r="4" fill="#8B5CF6"/>
            <path d="M12 14C8.13401 14 5 17.134 5 21H19C19 17.134 15.866 14 12 14Z" fill="#8B5CF6" opacity="0.7"/>
        </svg>
        @break

    @case('building')
        <svg class="group-hover:!text-primary" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M5 2H19C20.1046 2 21 2.89543 21 4V20C21 21.1046 20.1046 22 19 22H5C3.89543 22 3 21.1046 3 20V4C3 2.89543 3.89543 2 5 2Z" fill="#8B5CF6" opacity="0.3"/>
            <path d="M8 6H10V8H8V6Z" fill="#8B5CF6"/>
            <path d="M8 10H10V12H8V10Z" fill="#8B5CF6"/>
            <path d="M8 14H10V16H8V14Z" fill="#8B5CF6"/>
            <path d="M14 6H16V8H14V6Z" fill="#8B5CF6"/>
            <path d="M14 10H16V12H14V10Z" fill="#8B5CF6"/>
            <path d="M14 14H16V16H14V14Z" fill="#8B5CF6"/>
            <path d="M8 18H16V20H8V18Z" fill="#8B5CF6"/>
        </svg>
        @break

    @case('shield')
        <svg class="group-hover:!text-primary" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 2L3 5V11C3 16.55 6.84 21.74 12 23C17.16 21.74 21 16.55 21 11V5L12 2Z" fill="#8B5CF6"/>
            <path d="M12 6L7 8V11C7 14.5 9.14 17.87 12 19C14.86 17.87 17 14.5 17 11V8L12 6Z" fill="#8B5CF6" opacity="0.4"/>
        </svg>
        @break

    @case('role')
        <svg class="group-hover:!text-primary" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 12C14.7614 12 17 9.76142 17 7C17 4.23858 14.7614 2 12 2C9.23858 2 7 4.23858 7 7C7 9.76142 9.23858 12 12 12Z" fill="#8B5CF6"/>
            <path d="M12 14C8.13401 14 5 17.134 5 21H19C19 17.134 15.866 14 12 14Z" fill="#8B5CF6" opacity="0.7"/>
        </svg>
        @break

    @case('credit-card')
        <svg class="group-hover:!text-primary" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="2" y="5" width="20" height="14" rx="2" fill="#8B5CF6"/>
            <rect x="2" y="8" width="20" height="3" fill="#8B5CF6" opacity="0.3"/>
            <rect x="4" y="15" width="4" height="2" rx="1" fill="#8B5CF6"/>
            <rect x="10" y="15" width="4" height="2" rx="1" fill="#8B5CF6"/>
        </svg>
        @break

    @case('truck')
        <svg class="group-hover:!text-primary" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M20 8H17V4H3C1.89543 4 1 4.89543 1 6V17H3C3 18.6569 4.34315 20 6 20C7.65685 20 9 18.6569 9 17H15C15 18.6569 16.3431 20 18 20C19.6569 20 21 18.6569 21 17H23V12L20 8Z" fill="#8B5CF6"/>
            <circle cx="6" cy="17" r="2" fill="#8B5CF6" opacity="0.5"/>
            <circle cx="18" cy="17" r="2" fill="#8B5CF6" opacity="0.5"/>
            <path d="M17 8V12H21V8H17Z" fill="#8B5CF6" opacity="0.7"/>
        </svg>
        @break

    @case('newspaper')
        <svg class="group-hover:!text-primary" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M19 3H5C3.89543 3 3 3.89543 3 5V19C3 20.1046 3.89543 21 5 21H19C20.1046 21 21 20.1046 21 19V5C21 3.89543 20.1046 3 19 3Z" fill="#8B5CF6" opacity="0.2"/>
            <path d="M7 7H17V9H7V7Z" fill="#8B5CF6"/>
            <path d="M7 11H17V13H7V11Z" fill="#8B5CF6"/>
            <path d="M7 15H13V17H7V15Z" fill="#8B5CF6" opacity="0.7"/>
        </svg>
        @break

    @case('app')
        <svg class="group-hover:!text-primary" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="5" y="2" width="14" height="20" rx="2" fill="#8B5CF6" opacity="0.3"/>
            <rect x="7" y="4" width="10" height="12" rx="1" fill="#8B5CF6"/>
            <circle cx="12" cy="18" r="1" fill="#8B5CF6"/>
        </svg>
        @break

    @case('faq')
        <svg class="group-hover:!text-primary" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="12" cy="12" r="10" fill="#8B5CF6" opacity="0.2"/>
            <path d="M12 17C12.5523 17 13 16.5523 13 16C13 15.4477 12.5523 15 12 15C11.4477 15 11 15.4477 11 16C11 16.5523 11.4477 17 12 17Z" fill="#8B5CF6"/>
            <path d="M12 6C10.3431 6 9 7.34315 9 9V10H15V9C15 7.34315 13.6569 6 12 6Z" fill="#8B5CF6"/>
            <path d="M9 12H15V13H9V12Z" fill="#8B5CF6"/>
        </svg>
        @break

    @case('file-manager')
        <svg class="group-hover:!text-primary" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M10 4H4C2.89543 4 2 4.89543 2 6V20C2 21.1046 2.89543 22 4 22H20C21.1046 22 22 21.1046 22 20V8C22 6.89543 21.1046 6 20 6H12L10 4Z" fill="#8B5CF6"/>
            <path d="M9 12H15V14H9V12Z" fill="#8B5CF6" opacity="0.7"/>
            <path d="M9 16H15V18H9V16Z" fill="#8B5CF6" opacity="0.7"/>
        </svg>
        @break

    @case('custom-fields')
        <svg class="group-hover:!text-primary" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="12" cy="5" r="2" fill="#8B5CF6"/>
            <circle cx="12" cy="12" r="2" fill="#8B5CF6" opacity="0.7"/>
            <circle cx="12" cy="19" r="2" fill="#8B5CF6"/>
            <path d="M14 5H20V7H14V5Z" fill="#8B5CF6" opacity="0.4"/>
            <path d="M14 12H20V14H14V12Z" fill="#8B5CF6" opacity="0.4"/>
            <path d="M14 19H20V21H14V19Z" fill="#8B5CF6" opacity="0.4"/>
            <path d="M4 5H10V7H4V5Z" fill="#8B5CF6" opacity="0.4"/>
            <path d="M4 12H10V14H4V12Z" fill="#8B5CF6" opacity="0.4"/>
            <path d="M4 19H10V21H4V19Z" fill="#8B5CF6" opacity="0.4"/>
        </svg>
        @break

    @case('settings')
        <svg class="group-hover:!text-primary" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="12" cy="12" r="3" fill="#8B5CF6"/>
            <path d="M12 2C13.1 2 14.1 2.2 15 2.5V4.5C14.1 4.2 13.1 4 12 4C10.9 4 9.9 4.2 9 4.5V2.5C9.9 2.2 10.9 2 12 2ZM2 12C2 10.9 2.2 9.9 2.5 9H4.5C4.2 9.9 4 10.9 4 12C4 13.1 4.2 14.1 4.5 15H2.5C2.2 14.1 2 13.1 2 12ZM12 22C10.9 22 9.9 21.8 9 21.5V19.5C9.9 19.8 10.9 20 12 20C13.1 20 14.1 19.8 15 19.5V21.5C14.1 21.8 13.1 22 12 22ZM22 12C22 13.1 21.8 14.1 21.5 15H19.5C19.8 14.1 20 13.1 20 12C20 10.9 19.8 9.9 19.5 9H21.5C21.8 9.9 22 10.9 22 12Z" fill="#8B5CF6" opacity="0.7"/>
        </svg>
        @break

    @case('audit')
        <svg class="group-hover:!text-primary" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M14 2H6C5.46957 2 4.96086 2.21071 4.58579 2.58579C4.21071 2.96086 4 3.46957 4 4V20C4 20.5304 4.21071 21.0391 4.58579 21.4142C4.96086 21.7893 5.46957 22 6 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V8L14 2Z" fill="#8B5CF6" opacity="0.3"/>
            <path d="M14 2V8H20" fill="#8B5CF6" opacity="0.7"/>
            <path d="M16 13H8V15H16V13Z" fill="#8B5CF6"/>
            <path d="M16 17H8V19H16V17Z" fill="#8B5CF6"/>
            <circle cx="12" cy="12" r="10" stroke="#8B5CF6" stroke-width="2" fill="none" opacity="0.3"/>
        </svg>
        @break

    @case('logs')
        <svg class="group-hover:!text-primary" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="8" cy="8" r="2" fill="#8B5CF6"/>
            <path d="M8 12H8.01" stroke="#8B5CF6" stroke-width="2" stroke-linecap="round"/>
            <path d="M8 16H8.01" stroke="#8B5CF6" stroke-width="2" stroke-linecap="round"/>
            <path d="M8 20H8.01" stroke="#8B5CF6" stroke-width="2" stroke-linecap="round"/>
            <path d="M12 8H20" stroke="#8B5CF6" stroke-width="2" stroke-linecap="round"/>
            <path d="M12 12H20" stroke="#8B5CF6" stroke-width="2" stroke-linecap="round"/>
            <path d="M12 16H20" stroke="#8B5CF6" stroke-width="2" stroke-linecap="round"/>
            <path d="M12 20H20" stroke="#8B5CF6" stroke-width="2" stroke-linecap="round"/>
            <path d="M4 4H20V20H4V4Z" stroke="#8B5CF6" stroke-width="2" opacity="0.3"/>
        </svg>
        @break

    @default
        <svg class="group-hover:!text-primary" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="12" cy="12" r="10" stroke="#8B5CF6" stroke-width="2"/>
            <path d="M12 8V12L15 15" stroke="#8B5CF6" stroke-width="2" stroke-linecap="round"/>
        </svg>
        @break
@endswitch
