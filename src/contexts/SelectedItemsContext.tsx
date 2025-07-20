// SelectedItemsContext.js
import { createContext, useState, useContext, ReactNode } from 'react';

type SelectedItemsState = {
    [tableKey: string]: (string | number)[]; // Replace string[] with the appropriate type for your item IDs
};

type SelectedItemsContextType = {
    selectedItems: SelectedItemsState;
    setSelectedItems: React.Dispatch<React.SetStateAction<SelectedItemsState>>;
};

const SelectedItemsContext = createContext<SelectedItemsContextType | undefined>(undefined);


interface Props {
    children: ReactNode; // Use ReactNode for children prop to allow more flexible child types
}

export const SelectedItemsProvider: React.FC<Props> = ({ children }) => {
    const [selectedItems, setSelectedItems] = useState<SelectedItemsState>({});

    return (
        <SelectedItemsContext.Provider value={{ selectedItems, setSelectedItems }}>
            {children}
        </SelectedItemsContext.Provider>
    );
};

export const useSelectedItems = () => {
    const context = useContext(SelectedItemsContext);
    if (!context) {
        throw new Error('useSelectedItems must be used within a SelectedItemsProvider');
    }
    return context;
};
