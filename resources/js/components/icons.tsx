import type { ReactNode, SVGProps } from 'react';

// One stroke icon set for the whole app: 24px grid, 1.75 stroke, round caps.
type IconProps = SVGProps<SVGSVGElement>;

function Icon({ children, ...props }: IconProps & { children: ReactNode }) {
    return (
        <svg
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth={1.75}
            strokeLinecap="round"
            strokeLinejoin="round"
            aria-hidden="true"
            {...props}
        >
            {children}
        </svg>
    );
}

export function SearchIcon(props: IconProps) {
    return (
        <Icon {...props}>
            <circle cx="11" cy="11" r="6.5" />
            <path d="m16 16 4.5 4.5" />
        </Icon>
    );
}

/** Three game cases on a shelf. */
export function LibraryIcon(props: IconProps) {
    return (
        <Icon {...props}>
            <rect x="3.5" y="4" width="4.5" height="15" rx="1" />
            <rect x="9.75" y="4" width="4.5" height="15" rx="1" />
            <path d="m16.4 5.2 4.3 1.2-3.6 13.1-4.3-1.2" />
            <path d="M2.5 20.5h19" />
        </Icon>
    );
}

/** A ranked table: the journal's rows. */
export function JournalIcon(props: IconProps) {
    return (
        <Icon {...props}>
            <path d="M4 6h2M4 12h2M4 18h2" />
            <path d="M9.5 6H20M9.5 12H20M9.5 18H16" />
        </Icon>
    );
}

export function UserIcon(props: IconProps) {
    return (
        <Icon {...props}>
            <circle cx="12" cy="8.5" r="4" />
            <path d="M4.5 20.5c1.2-3.6 4-5.5 7.5-5.5s6.3 1.9 7.5 5.5" />
        </Icon>
    );
}

export function LogOutIcon(props: IconProps) {
    return (
        <Icon {...props}>
            <path d="M14 4.5H6.5a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2H14" />
            <path d="m17 8 4 4-4 4M21 12H10" />
        </Icon>
    );
}

export function SettingsIcon(props: IconProps) {
    return (
        <Icon {...props}>
            <path d="M4 7h10M18 7h2M4 17h4M12 17h8" />
            <circle cx="16" cy="7" r="2" />
            <circle cx="10" cy="17" r="2" />
        </Icon>
    );
}

export function ChevronLeftIcon(props: IconProps) {
    return (
        <Icon {...props}>
            <path d="m14.5 6-6 6 6 6" />
        </Icon>
    );
}

export function ChevronDownIcon(props: IconProps) {
    return (
        <Icon {...props}>
            <path d="m6 9.5 6 6 6-6" />
        </Icon>
    );
}

export function ChevronRightIcon(props: IconProps) {
    return (
        <Icon {...props}>
            <path d="m9.5 6 6 6-6 6" />
        </Icon>
    );
}

export function ExternalIcon(props: IconProps) {
    return (
        <Icon {...props}>
            <path d="M13.5 4.5h6v6M19.5 4.5 11 13" />
            <path d="M17 14v4a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 5 18V8.5A1.5 1.5 0 0 1 6.5 7h4" />
        </Icon>
    );
}

export function CloseIcon(props: IconProps) {
    return (
        <Icon {...props}>
            <path d="m6 6 12 12M18 6 6 18" />
        </Icon>
    );
}

export function CheckIcon(props: IconProps) {
    return (
        <Icon {...props}>
            <path d="m5 12.5 4.5 4.5L19 7.5" />
        </Icon>
    );
}

export function AlertIcon(props: IconProps) {
    return (
        <Icon {...props}>
            <path d="M12 3.5 21.5 20h-19z" />
            <path d="M12 10v4M12 17.2v.1" />
        </Icon>
    );
}
