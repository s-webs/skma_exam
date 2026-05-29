import { cn } from '@/lib/utils';

interface AnswerOptionProps {
    id: number;
    content: string;
    imageUrl: string | null;
    selected: boolean;
    onSelect: () => void;
}

export function AnswerOption({ content, imageUrl, selected, onSelect }: AnswerOptionProps) {
    return (
        <button
            type="button"
            onClick={onSelect}
            className={cn(
                'w-full rounded-xl border-2 p-4 text-left transition active:scale-[0.98]',
                selected
                    ? 'border-indigo-600 bg-indigo-50 ring-2 ring-indigo-200'
                    : 'border-gray-200 bg-white hover:border-indigo-300',
            )}
        >
            {imageUrl && (
                <img
                    src={imageUrl}
                    alt=""
                    className="mx-auto mb-3 max-h-[30vh] w-auto object-contain"
                />
            )}
            {content ? <p className="text-base leading-relaxed text-gray-900">{content}</p> : null}
        </button>
    );
}
