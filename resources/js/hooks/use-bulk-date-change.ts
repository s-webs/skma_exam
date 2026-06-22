import { router } from '@inertiajs/react';

export function useBulkDateChange(selected: number[], clearSelection: () => void) {
    const selectedCount = selected.length;

    const bulkUpdateDate = () => {
        if (selected.length === 0) {
            return;
        }

        const uniqueIds = [...new Set(selected)];

        const date = prompt(
            `Введите новую дату для ${uniqueIds.length} записей (ГГГГ-ММ-ДД):`,
            new Date().toISOString().slice(0, 10),
        );

        if (!date) {
            return;
        }

        if (!/^\d{4}-\d{2}-\d{2}$/.test(date)) {
            alert('Неверный формат даты. Используйте ГГГГ-ММ-ДД.');

            return;
        }

        router.post(
            route('admin.exam-registrations.bulk-update-date'),
            { registration_ids: uniqueIds, date },
            { onSuccess: () => clearSelection() },
        );
    };

    return {
        selectedCount,
        bulkUpdateDate,
    };
}
