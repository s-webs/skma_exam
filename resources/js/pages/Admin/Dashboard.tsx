import { Head } from '@inertiajs/react';
import { Users, UserCheck, UserX, Activity } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

interface DashboardProps {
    auth: {
        user: {
            name: string;
            email: string;
            roles: Array<{ name: string }>;
        };
    };
}

export default function Dashboard({ auth }: DashboardProps) {
    const userRole = auth.user.roles[0]?.name || 'user';

    return (
        <AppLayout>
            <Head title="Dashboard" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="mb-6">
                        <h2 className="text-3xl font-bold tracking-tight">Dashboard</h2>
                        <p className="text-muted-foreground mt-2">
                            Welcome back, {auth.user.name}! You are logged in as <span className="font-semibold">{userRole}</span>.
                        </p>
                    </div>

                    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Total Applicants</CardTitle>
                                <Users className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">0</div>
                                <p className="text-muted-foreground text-xs">No applicants yet</p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Approved</CardTitle>
                                <UserCheck className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">0</div>
                                <p className="text-muted-foreground text-xs">Waiting for approval</p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Pending</CardTitle>
                                <UserX className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">0</div>
                                <p className="text-muted-foreground text-xs">Need review</p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Active Exams</CardTitle>
                                <Activity className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">0</div>
                                <p className="text-muted-foreground text-xs">In progress</p>
                            </CardContent>
                        </Card>
                    </div>

                    <div className="mt-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Quick Actions</CardTitle>
                                <CardDescription>Common tasks based on your role</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-2">
                                    {userRole === 'developer' && (
                                        <p className="text-sm">
                                            As a <span className="font-semibold">Developer</span>, you have full access to manage users, applicants, questions, and system settings.
                                        </p>
                                    )}
                                    {userRole === 'ktbo' && (
                                        <p className="text-sm">
                                            As <span className="font-semibold">KTBO</span>, you can manage applicants, approve exams, and manage questions.
                                        </p>
                                    )}
                                    {userRole === 'registrator' && (
                                        <p className="text-sm">
                                            As a <span className="font-semibold">Registrator</span>, you can view and register new applicants.
                                        </p>
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
