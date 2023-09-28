import React, { useEffect, useState } from "react";
import Select from "react-select";
import useAxios from "@/hooks/useAxios";

const ConditionalDropdown = () => {
    const { get: getCustomers } = useAxios();
    const { get: getIssueSources } = useAxios();
    const { get: getIssueCategories } = useAxios();

    const [selectedCustomer, setSelectedCustomer] = useState(null);
    const [selectedIssueSource, setSelectedIssueSource] = useState(null);
    const [selectedIssueCategory, setSelectedIssueCategory] = useState(null);
    const [customers, setCustomers] = useState([]);
    const [issueSources, setIssueSources] = useState([]);
    const [issueCategories, setIssueCategories] = useState([]);

    useEffect(() => {
        getCustomers('admin/customers?all=1').then((resp:any) => resp && setCustomers(resp))
        
    }, [])

    const fetchIssueSourcesForCustomer = async (customerId) => {
        try {
            const issueSourcesResponse = await getIssueSources(`admin/doctors/?all=1&customer_id=${customerId}`);
            setIssueSources(issueSourcesResponse);
        } catch (error) {
            console.error("Error fetching issue sources:", error);
        }
    };

    const fetchIssueCategoriesForIssueSource = async (issueSourceId) => {
        try {
            const issueCategoriesResponse = await getIssueCategories(`admin/settings/picklists/tickets/issuecategories?all=1&issue_source_id=${issueSourceId}`);
            setIssueCategories(issueCategoriesResponse);
        } catch (error) {
            console.error("Error fetching issue categories:", error);
        }
    };

    const handleCustomerChange = (selectedCustomer) => {
        setSelectedCustomer(selectedCustomer);
        setSelectedIssueSource(null);
        setSelectedIssueCategory(null);
        setIssueSources([]);
        setIssueCategories([]);

        if (selectedCustomer) {
            fetchIssueSourcesForCustomer(selectedCustomer.id);
        }
    };

    const handleIssueSourceChange = (selectedIssueSource) => {
        setSelectedIssueSource(selectedIssueSource);
        setSelectedIssueCategory(null);
        setIssueCategories([]);

        if (selectedIssueSource) {
            fetchIssueCategoriesForIssueSource(selectedIssueSource.id);
        }
    };

    return (
        <div>
            <p>Select a Customer</p>
            <Select
                value={selectedCustomer}
                onChange={handleCustomerChange}
                options={customers}
                getOptionValue={(option) => `${option['id']}`}
                getOptionLabel={(option) => `${option['name']}`}
            />
            <p>Select an Issue Source</p>
            <Select
                value={selectedIssueSource}
                onChange={handleIssueSourceChange}
                options={issueSources}
                getOptionValue={(option) => `${option['id']}`}
                getOptionLabel={(option) => `${option['name']}`}
            />
            <p>Select an Issue Category</p>
            <Select
                value={selectedIssueCategory}
                onChange={setSelectedIssueCategory}
                options={issueCategories}
                getOptionValue={(option) => `${option['id']}`}
                getOptionLabel={(option) => `${option['name']}`}
            />
        </div>
    );
};

export default ConditionalDropdown;
