import { Head, Link } from '@inertiajs/react';
import { ExternalLink, Copy, Check } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface Exam {
    id: number;
    name: string;
    language: string;
}

interface ExamType {
    id: number;
    name: string;
    slug: string;
    exams: Exam[];
}

interface RegistrationLinkDialogProps {
    examType: ExamType;
}

export function RegistrationLinkDialog({ examType }: RegistrationLinkDialogProps) {
    const [copied, setCopied] = useState(false);
    const registrationUrl = `${window.location.origin}/register/${examType.slug}`;

    const copyToClipboard = () => {
        navigator.clipboard.writeText(registrationUrl);
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
    };

    return (
        <Dialog>
            <DialogTrigger asChild>
                <Button variant="outline" size="sm">
                    <ExternalLink className="mr-2 h-4 w-4" />
                    Ссылка на регистрацию
                </Button>
            </DialogTrigger>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Ссылка для регистрации абитуриентов</DialogTitle>
                    <DialogDescription>
                        Отправьте эту ссылку абитуриентам для регистрации на экзамен "{examType.name}"
                    </DialogDescription>
                </DialogHeader>
                <div className="space-y-4">
                    <div className="space-y-2">
                        <Label>URL для регистрации</Label>
                        <div className="flex gap-2">
                            <Input value={registrationUrl} readOnly />
                            <Button
                                type="button"
                                size="icon"
                                variant="outline"
                                onClick={copyToClipboard}
                            >
                                {copied ? (
                                    <Check className="h-4 w-4 text-green-600" />
                                ) : (
                                    <Copy className="h-4 w-4" />
                                )}
                            </Button>
                        </div>
                    </div>

                    <div className="rounded-lg border bg-muted/50 p-4">
                        <p className="text-sm font-medium mb-2">Доступные экзамены:</p>
                        <ul className="space-y-1">
                            {examType.exams.map((exam) => (
                                <li key={exam.id} className="text-sm text-muted-foreground">
                                    • {exam.name}
                                </li>
                            ))}
                        </ul>
                    </div>

                    <div className="rounded-lg border border-yellow-200 bg-yellow-50 p-3">
                        <p className="text-sm text-yellow-800">
                            <strong>Важно:</strong> После регистрации абитуриенты не получат автоматический доступ к экзамену.
                            Регистратор должен вручную одобрить каждую заявку.
                        </p>
                    </div>

                    <div className="flex justify-end">
                        <Link href={registrationUrl} target="_blank">
                            <Button variant="outline">
                                <ExternalLink className="mr-2 h-4 w-4" />
                                Открыть страницу регистрации
                            </Button>
                        </Link>
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    );
}
