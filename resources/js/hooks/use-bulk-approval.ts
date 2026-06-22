import { useMemo, useState } from 'react';
import { router } from '@inertiajs/react';

interface BulkApprovalRow {
    registration_id: number;
    approved: boolean;
}

export function useBulkApproval(rows: BulkApprovalRow[]) {
    const [selected, setSelected] = useState<number[]>([]);

    const pendingIds = useMemo(
        () => rows.filter((row) => !row.approved).map((row) => row.registration_id),
        [rows],
    );

    const allPendingSelected =
        pendingIds.length > 0 && pendingIds.every((id) => selected.includes(id));

    const selectedPendingCount = selected.filter((id) => pendingIds.includes(id)).length;

    const toggle = (registrationId: number) => {
        setSelected((current) =>
            current.includes(registrationId)
                ? current.filter((id) => id !== registrationId)
                : [...current, registrationId],
        );
    };

    const toggleAllPending = () => {
        setSelected(allPendingSelected ? [] : pendingIds);
    };

    const bulkApprove = () => {
        const ids = selected.filter((id) => pendingIds.includes(id));

        if (ids.length === 0) {
            return;
        }

        if (
            !confirm(
                `Одобрить ${ids.length} записей? Абитуриентам будут отправлены ссылки на экзамен.`,
            )
        ) {
            return;
        }

        router.post(
            route('admin.exam-registrations.bulk-approve'),
            { registration_ids: ids },
            { onSuccess: () => setSelected([]) },
        );
    };

    return {
        selected,
        setSelected,
        pendingIds,
        allPendingSelected,
        selectedPendingCount,
        toggle,
        toggleAllPending,
        bulkApprove,
        clearSelection: () => setSelected([]),
    };
}
