import ReactDOM from "react-dom/client";
import "./styles/index.scss";
import { AuthProvider } from "@/contexts/AuthContext";
import { RouterProvider } from "react-router-dom";
import router from "@/routes/router.js";
import ErrorBoundary from "./components/Notifications/ErrorBoundary";
import './scss/customized-boostrap.scss';
import 'react-toastify/dist/ReactToastify.css';

import { RolePermissionsProvider } from "./contexts/RolePermissionsContext";

ReactDOM.createRoot(document.getElementById("root")!).render(
  <ErrorBoundary fallback="There was an error.">
    <AuthProvider>
      <RolePermissionsProvider>
        <RouterProvider router={router} />
      </RolePermissionsProvider>
    </AuthProvider>
  </ErrorBoundary>
);
