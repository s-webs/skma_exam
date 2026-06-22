import { Head, usePage } from '@inertiajs/react';
import { Users, UserCheck, UserX, Activity } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

interface SharedAuth {
    auth: {
        user: {
            name: string;
            email: string;
            roles: Array<{ name: string }>;
        };
        roles?: string[];
    };
}

export default function Dashboard() {
    const { t } = useTranslation();
    const { auth } = usePage<SharedAuth>().props;
    const userRole = auth.roles?.[0] ?? auth.user.roles[0]?.name ?? 'user';

    return (
        <AppLayout>
            <Head title={t('dashboard.title')} />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="mb-6">
                        <h2 className="text-3xl font-bold tracking-tight">{t('dashboard.title')}</h2>
                        <p className="text-muted-foreground mt-2">
                            {t('dashboard.welcome', { name: auth.user.name })} <span className="font-semibold">{userRole}</span>.
                        </p>
                    </div>

                    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">{t('dashboard.totalApplicants')}</CardTitle>
                                <Users className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">0</div>
                                <p className="text-muted-foreground text-xs">{t('dashboard.noApplicants')}</p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">{t('dashboard.approved')}</CardTitle>
                                <UserCheck className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">0</div>
                                <p className="text-muted-foreground text-xs">{t('dashboard.waitingApproval')}</p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">{t('dashboard.pending')}</CardTitle>
                                <UserX className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">0</div>
                                <p className="text-muted-foreground text-xs">{t('dashboard.needReview')}</p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">{t('dashboard.activeExams')}</CardTitle>
                                <Activity className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">0</div>
                                <p className="text-muted-foreground text-xs">{t('dashboard.inProgress')}</p>
                            </CardContent>
                        </Card>
                    </div>

                    <div className="mt-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>{t('dashboard.quickActions')}</CardTitle>
                                <CardDescription>{t('dashboard.quickActionsDesc')}</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-2">
                                    {userRole === 'developer' && (
                                        <p className="text-sm">{t('dashboard.developerDesc')}</p>
                                    )}
                                    {userRole === 'ktbo' && (
                                        <p className="text-sm">{t('dashboard.ktboDesc')}</p>
                                    )}
                                    {userRole === 'registrator' && (
                                        <p className="text-sm">{t('dashboard.registratorDesc')}</p>
                                    )}
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
