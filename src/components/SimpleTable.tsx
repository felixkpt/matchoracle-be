import Str from "@/utils/Str";

type Props = {
    record: any
    exclude?: string[]
    only?: string[]
}

function SimpleTable({ record, exclude, only }: Props) {

    let recordFiltered: any = record;
    if (only && only.length > 0) {
        // Filter the record object to include only keys listed in the 'only' prop
        recordFiltered = Object.keys(record).reduce((filtered: any, key: string) => {
            if (only.includes(key) || key === 'id') {
                filtered[key] = record[key];
            }
            return filtered;
        }, {});
    }

    return (
        <div>
            <table className="table table-striped table-bordered rounded">
                <tbody>
                    {Object.keys(recordFiltered).map((key) => {
                        if (exclude && exclude.length > 0 && exclude.includes(key)) {
                            return null; // Skip this key if it's in the exclude array
                        }
                        return (
                            <tr key={key}>
                                <td className="p-2">
                                    <div className="d-flex flex-column gap-2">
                                        <div className="d-flex">
                                            <span className="fw-bold me-2">{Str.title(key)}:</span>
                                            <span>
                                                {
                                                    recordFiltered[key] && Array.isArray(recordFiltered[key]) ?
                                                        recordFiltered[key].reduce((prev: any, item: any) => (prev ? prev + ', ' : prev) + item.name, '')
                                                        : recordFiltered[key]
                                                }
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td className="w-1"></td>
                            </tr>
                        );
                    })}
                </tbody>
            </table>

        </div>
    );
}

export default SimpleTable;
