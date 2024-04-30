import Str from "@/utils/Str";
import AutoActions from "./AutoActions";
import { useNavigate } from "react-router-dom";
import { useEffect } from "react";
import { CollectionItemsInterface, ListSourceInterface } from "@/interfaces/UncategorizedInterfaces";

type Props = {
    record: any
    exclude?: string[]
    only?: string[]
    htmls?: string[]
    listSources?: { [key: string]: () => Promise<ListSourceInterface[]> }
    modelDetails?: CollectionItemsInterface

}

// Define the __dangerousHtml function
function __dangerousHtml(html: HTMLElement) {
    // Implement the logic to safely render HTML content here
    return <div dangerouslySetInnerHTML={{ __html: html }} />;
}

function SimpleTable({ record, exclude, only, htmls, listSources, modelDetails }: Props) {

    const isNative = !!modelDetails

    let allExcluded: string[] = ['status', 'status_id', 'user_id', 'action']
    if (exclude && exclude.length > 0)
        allExcluded.push(...exclude)

    let allHtmls: string[] = ['Status']
    if (htmls && htmls.length > 0)
        allHtmls.push(...htmls)
    if (modelDetails && modelDetails.htmls)
        allHtmls.push(...modelDetails.htmls)

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

    const navigate = useNavigate()

    const autoActions = new AutoActions(modelDetails, record, navigate, listSources, exclude)

    useEffect(() => {

        const autotableNavigateElements = document.querySelectorAll('.autotable .autotable-navigate');
        autotableNavigateElements.forEach((element) => {
            element.addEventListener('click', autoActions.handleNavigation);
        });

        const autotableViewElements = document.querySelectorAll('.autotable .autotable-modal-view');
        autotableViewElements.forEach((element) => {
            element.addEventListener('click', autoActions.handleView);
        });

        const autotableModalActionElements = document.querySelectorAll('.autotable [class*="autotable-modal-"]');
        autotableModalActionElements.forEach((element) => {
            element.addEventListener('click', autoActions.handleModalAction);
        });

        return () => {
            // Clean up event listeners when the component unmounts
            autotableViewElements.forEach((element) => {
                element.removeEventListener('click', autoActions.handleView);
            });

            autotableNavigateElements.forEach((element) => {
                element.removeEventListener('click', autoActions.handleNavigation);
            });

            autotableModalActionElements.forEach((element) => {
                element.removeEventListener('click', autoActions.handleModalAction);
            });
        };

    }, [navigate, record]);

    return (
        <div className="autotable">
            {
                isNative && recordFiltered?.action &&
                <div className="d-flex justify-content-end my-2">
                    <div className="d-flex gap-2 align-items-center">Actions: {__dangerousHtml(recordFiltered?.action)}</div>
                </div>
            }
            <table className="table table-striped table-bordered rounded">
                <tbody>
                    {Object.keys(recordFiltered).map((key) => {
                        const value = recordFiltered[key]

                        if (allExcluded && allExcluded.length > 0 && allExcluded.includes(key)) {
                            return null; // Skip this key if it's in the exclude array
                        }

                        return (
                            <tr key={key}>
                                <td className="p-2">
                                    <div className="d-flex flex-column gap-2">
                                        <div className="d-flex w-100">
                                            <div>
                                                {
                                                    value && Array.isArray(value) ?
                                                        <div>
                                                            <span className="fw-bold me-2 text-nowrap">{Str.title(key)}:</span>
                                                            <div className="row">

                                                                {
                                                                    value.slice(0, 8).map((item: any) => <div key={item.id} className={`${value.slice(0, 8).length === 8 ? 'col-3' : 'col-6'}`}><SimpleTable record={item} /></div>)

                                                                }
                                                                {
                                                                    value.length > 8 && <div className="col-12">...({value.length - 8} truncated)</div>
                                                                }
                                                            </div>
                                                        </div>
                                                        : <div className="d-flex">
                                                            <span className="fw-bold me-2 text-nowrap">{Str.title(key)}:</span>
                                                            {
                                                                allHtmls?.includes(key) ? __dangerousHtml(value || 'N/A') : (
                                                                    <div>
                                                                        {
                                                                            (typeof value === 'object' && value !== null) ?
                                                                                <SimpleTable record={value} />
                                                                                :
                                                                                String(value)
                                                                        }
                                                                    </div>
                                                                ) || 'N/A'
                                                            }
                                                        </div>
                                                }
                                            </div>
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
