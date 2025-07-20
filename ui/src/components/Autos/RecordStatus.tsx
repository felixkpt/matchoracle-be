import Str from '@/utils/Str';
import { Icon } from '@iconify/react';

type Props = {
    key?: string, row: any, statuses: any
}

const RecordStatus = ({ row, statuses }: Props) => {

    const status = statuses.find((itm: any) => itm.id == row.status_id);

    if (!status) return null

    return (
        <div className="d-flex align-items-center gap-1"><Icon icon={status.icon || 'mdi-light:home'} className={status.class}></Icon>{Str.title(status.name)}</div>
    );

}

export default RecordStatus