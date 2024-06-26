import { ReactNode } from "react";

export interface UserInterface {
    [x: string]: ReactNode;
    id: number;
    name: string;
    email: string;
    roles: []
    avatar: string | null
}

// Define a generic interface for the authenticated user object
export interface AuthenticatedUser {
    // The authenticated user or null if not authenticated
    user?: UserInterface | null;
    // Function to update the user object
    updateUser: (updatedUser: Partial<UserInterface>) => void;
    // Function to generate CSRF token for guest methods
    csrfToken: () => Promise<boolean>;
    // Function to set the user object in the state
    setUser: (user: UserInterface) => void;
    // Function to delete user data and reset state
    deleteUser: () => void;
    // recenty verified user credentials
    verified: boolean
    setVerified: (val: boolean) => void;
    redirectTo: string
    setRedirectTo: (location: string) => void;
    redirectMessage: string | undefined
    setRedirectMessage: (message: string) => void;
  }