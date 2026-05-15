import { useSidebar } from '@/components/ui/sidebar';

export default function AppLogo() {
    const { state } = useSidebar();
    const isCollapsed = state === 'collapsed';

    return (
        <div className="w-full py-6 overflow-hidden relative">
            <div
                className="flex transition-transform duration-300 ease-in-out"
                style={{ transform: isCollapsed ? 'translateX(-100%)' : 'translateX(0)' }}
            >
                {/* Большой логотип */}
                <div className="w-full flex-shrink-0 px-4 flex items-center justify-center" style={{ height: '63px' }}>
                    <img
                        src="/assets/images/skma-exam-logo.svg"
                        alt="SKMA Exam Logo"
                        className="w-full h-full object-contain"
                    />
                </div>
                {/* Маленький логотип */}
                <div className="w-full flex-shrink-0 flex items-center justify-center" style={{ height: '63px' }}>
                    <img
                        src="/assets/images/skma-small-logo.svg"
                        alt="SKMA Logo"
                        className="w-full h-full object-contain"
                    />
                </div>
            </div>
        </div>
    );
}
