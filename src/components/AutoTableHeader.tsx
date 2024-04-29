import Str from "@/utils/Str";
import { Icon } from "@iconify/react/dist/iconify.js";
import { ColumnInterface } from "../interfaces/UncategorizedInterfaces"; // Assuming you have a Column interface defined

interface AutoTableHeaderProps {
    columns: ColumnInterface[];
    handleOrderBy: (key: string) => void;
    setColumns: (columns: ColumnInterface[]) => void;
}

const AutoTableHeader: React.FC<AutoTableHeaderProps> = ({ columns, handleOrderBy, setColumns }) => {
    return columns.map((column) => {
        const { label, key, isSorted, sortDirection } = column;

        const handleHeaderClick = () => {
            const newColumns = columns.map((c) => ({
                ...c,
                isSorted: c.key === key,
                sortDirection: c.key === key ? (c.sortDirection === 'asc' ? 'desc' : 'asc') : '',
            }));

            handleOrderBy(key);
            setColumns(newColumns);
        };

        return (
            <th key={key} scope='col' className='border-top'>
                <span className='px-6 py-3 cursor-pointer' onClick={handleHeaderClick}>
                    {Str.title(label || key.split('.')[0])}
                </span>
                {isSorted && (
                    <span className='ml-1'>
                        {sortDirection === 'asc' ? (
                            <Icon icon="fluent:caret-up-20-filled" />)
                            : (
                                <Icon icon="fluent:caret-down-20-filled" />
                            )}
                    </span>
                )}
            </th>
        );
    });
};

export default AutoTableHeader;
