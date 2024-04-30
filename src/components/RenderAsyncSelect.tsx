import { PropsValue } from 'react-select';
import AsyncSelect from 'react-select/async';

import Str from '@/utils/Str';
import { useState } from "react";
import { ListSourceInterface } from '@/interfaces/UncategorizedInterfaces';

interface RenderAsyncSelectProps {
    current_key: string;
    currentData: {};
    isMulti?: boolean;
    listSources?: { [key: string]: () => Promise<ListSourceInterface[]> };
    listSelects?: { [key: string]: any };
}

var l: any

const RenderAsyncSelect = ({ listSources, listSelects, current_key, currentData, isMulti = false }: RenderAsyncSelectProps) => {

    if (listSources)
        l = listSources

    async function getOptions(current_key: string, rawSelected: PropsValue<object> | PropsValue<object[]> | undefined, q?: string) {
        
        if (!l) return {};

        const fn = Str.camel(current_key);

        // Type assertion to specify that listSources[fn] is a function returning Promise<any>
        const listSourceFn = l[fn] as ((q?: string) => Promise<any>);

        if (typeof listSourceFn === 'function') {
            const options = await listSourceFn(q);

            let selected = rawSelected;

            if (listSelects && listSelects[fn]) {
                selected = listSelects[fn]
            }
            else {

                if (Array.isArray(rawSelected)) {
                    selected = options.filter((option: any) => rawSelected.some((selectedItem: any) => String(selectedItem.id) === String(option.id)));
                } else if (typeof rawSelected === 'number' || typeof rawSelected === 'string') {

                    selected = options.find((option: any) => String(option.id) === String(rawSelected) || String(option.name) === String(rawSelected));
                }
            }

            return { options, selected: selected };

        } else {
            const err = `Function '${fn}' not found in listSources.`
            console.log(err)
            throw new Error(err);
        }
    }

    function loadOptions(q: string) {

        if (current_key) {

            async function fetchData() {
                const currentValue = typeof currentData === 'number' ? currentData : (currentData || (isMulti ? [] : ''));
                const { options: fetchedOptions, selected: fetchedSelected } = await getOptions(current_key, currentValue, q);

                setSelected(fetchedSelected);

                // Include the existing record's option in fetchedOptions if not already present
                if (currentValue && !fetchedOptions.some((option: any) => option.id === currentValue.id)) {
                    fetchedOptions.push(currentValue);
                }
                return fetchedOptions
            }

            return fetchData();

        }
    }

    const [selected, setSelected] = useState<PropsValue<object> | undefined>();

    return (
        <AsyncSelect
            id={current_key}
            className="form-control"
            name={
                isMulti && Array.isArray(selected) && selected.length > 0
                    ? `${current_key}[]`
                    : current_key
            }
            key={current_key}
            value={selected}
            isMulti={isMulti}
            defaultOptions
            loadOptions={(q: any) => loadOptions(q)}
            onChange={(val) => setSelected(val)}
            getOptionValue={(option: any) => `${option['id']}`}
            getOptionLabel={(option: any) => `${option['name']}`}
        />
    );
};

export default RenderAsyncSelect