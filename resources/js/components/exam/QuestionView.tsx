import { AnswerOption } from '@/components/exam/AnswerOption';

export interface ExamAnswer {
    id: number;
    content: string;
    image_url: string | null;
}

export interface ExamQuestion {
    id: number;
    order: number;
    content: string;
    image_url: string | null;
    answers: ExamAnswer[];
}

interface QuestionViewProps {
    question: ExamQuestion;
    selectedAnswerId: number | null;
    onSelectAnswer: (answerId: number) => void;
}

export function QuestionView({ question, selectedAnswerId, onSelectAnswer }: QuestionViewProps) {
    return (
        <div className="space-y-4">
            <div className="rounded-xl bg-white p-4 shadow-sm">
                <p className="text-xs font-medium uppercase tracking-wide text-indigo-600">
                    Вопрос {question.order}
                </p>
                {question.image_url && (
                    <img
                        src={question.image_url}
                        alt=""
                        className="mx-auto mt-3 max-h-[40vh] w-auto object-contain"
                    />
                )}
                {question.content ? (
                    <p className="mt-3 text-lg leading-relaxed text-gray-900">{question.content}</p>
                ) : null}
            </div>

            <div className="flex flex-col gap-3">
                {question.answers.map((answer) => (
                    <AnswerOption
                        key={answer.id}
                        id={answer.id}
                        content={answer.content}
                        imageUrl={answer.image_url}
                        selected={selectedAnswerId === answer.id}
                        onSelect={() => onSelectAnswer(answer.id)}
                    />
                ))}
            </div>
        </div>
    );
}
