import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { useTranslation } from 'react-i18next';

interface PermissionGroup {
    label: string;
    permissions: string[];
}

interface PermissionCheckboxesProps {
    permissionGroups: Record<string, PermissionGroup>;
    selected: string[];
    onChange: (permissions: string[]) => void;
    idPrefix?: string;
}

export function PermissionCheckboxes({
    permissionGroups,
    selected,
    onChange,
    idPrefix = 'perm',
}: PermissionCheckboxesProps) {
    const { t } = useTranslation();

    const toggle = (permission: string, checked: boolean) => {
        onChange(
            checked
                ? [...selected, permission]
                : selected.filter((name) => name !== permission),
        );
    };

    return (
        <div className="space-y-6">
            {Object.entries(permissionGroups).map(([groupKey, group]) => (
                <div key={groupKey} className="space-y-3">
                    <h4 className="text-sm font-medium">
                        {t(`permissions.groups.${group.label}`, { defaultValue: group.label })}
                    </h4>
                    <div className="grid gap-3 sm:grid-cols-2">
                        {group.permissions.map((permission) => {
                            const inputId = `${idPrefix}-${permission}`;

                            return (
                                <div key={permission} className="flex items-center gap-2">
                                    <Checkbox
                                        id={inputId}
                                        checked={selected.includes(permission)}
                                        onCheckedChange={(checked) =>
                                            toggle(permission, checked === true)
                                        }
                                    />
                                    <Label htmlFor={inputId} className="font-normal">
                                        {t(`permissions.names.${permission}`, {
                                            defaultValue: permission,
                                        })}
                                    </Label>
                                </div>
                            );
                        })}
                    </div>
                </div>
            ))}
        </div>
    );
}
