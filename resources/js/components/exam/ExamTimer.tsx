import { useEffect, useState } from 'react';
import { Clock } from 'lucide-react';
import { cn } from '@/lib/utils';

interface ExamTimerProps {
    expiresAt: string;
    onExpire?: () => void;
}

export function ExamTimer({ expiresAt, onExpire }: ExamTimerProps) {
    const [secondsLeft, setSecondsLeft] = useState(() =>
        Math.max(0, Math.floor((new Date(expiresAt).getTime() - Date.now()) / 1000)),
    );

    useEffect(() => {
        const tick = () => {
            const left = Math.max(
                0,
                Math.floor((new Date(expiresAt).getTime() - Date.now()) / 1000),
            );
            setSecondsLeft(left);
            if (left === 0) {
                onExpire?.();
            }
        };

        tick();
        const interval = setInterval(tick, 1000);
        return () => clearInterval(interval);
    }, [expiresAt, onExpire]);

    const minutes = Math.floor(secondsLeft / 60);
    const seconds = secondsLeft % 60;
    const urgent = secondsLeft > 0 && secondsLeft <= 300;

    return (
        <div
            className={cn(
                'flex items-center gap-1.5 rounded-full px-3 py-1 text-sm font-semibold tabular-nums',
                urgent ? 'bg-red-100 text-red-700' : 'bg-white/80 text-gray-800',
            )}
        >
            <Clock className="h-4 w-4 shrink-0" />
            <span>
                {String(minutes).padStart(2, '0')}:{String(seconds).padStart(2, '0')}
            </span>
        </div>
    );
}
