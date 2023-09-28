import React, { ReactNode } from "react";

interface IndexProps {
  showModal: boolean;
  setShowModal: (show: boolean) => void;
  size: "xl" | "lg" | "sm"; // Restrict to the specified values
  title: string | ReactNode; // Allow string or ReactNode for title
  body: ReactNode; // Allow only ReactNode for body
}

const Index: React.FC<IndexProps> = ({ showModal, setShowModal, size, title, body }) => {
  return (

    <div>

      {
        showModal &&


        <div id="toast-undo" class="flex items-center w-full max-w-xs p-4 text-gray-500 bg-white rounded-lg shadow dark:text-gray-400 dark:bg-gray-800" role="alert">
          <div class="text-sm font-normal">
            Conversation archived.
          </div>
          <div class="flex items-center ml-auto space-x-2">
            <a class="text-sm font-medium text-blue-600 p-1.5 hover:bg-blue-100 rounded-lg dark:text-blue-500 dark:hover:bg-gray-700" href="#">Undo</a>
            <button type="button" class="ml-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex items-center justify-center h-8 w-8 dark:text-gray-500 dark:hover:text-white dark:bg-gray-800 dark:hover:bg-gray-700" data-dismiss-target="#toast-undo" aria-label="Close">
              <span class="sr-only">Close</span>
              <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
              </svg>
            </button>
          </div>
        </div>
      }
    </div>
  );
};

export default Index;
