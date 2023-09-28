import Error403 from "@/Pages/ErrorPages/Error403";
import { useEffect } from "react";

// Function to conditionally render a component or redirect to 403
const renderComponent = (view, Component) => {
 
  return userCanView(view) ? <Component /> : <Error403 />;

};

const userCanView = (view: string) => {

  if (view !== 'aadmin')
    return true
  return false
}

export default renderComponent