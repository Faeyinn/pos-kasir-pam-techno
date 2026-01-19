        formatDateTime(dateTimeString) {
            if (!dateTimeString) return '-';
            
            const date = new Date(dateTimeString);
            
            // Format: DD/MM/YYYY HH:MM
            const day = date.getDate().toString().padStart(2, '0');
            const month = (date.getMonth() + 1).toString().padStart(2, '0');
            const year = date.getFullYear();
            const hours = date.getHours().toString().padStart(2, '0');
            const minutes = date.getMinutes().toString().padStart(2, '0');
            
            return `${day}/${month}/${year} ${hours}:${minutes}`;
        },

        formatForInput(dateTimeString) {
            if (!dateTimeString) return '';
            
            // Convert MySQL datetime "2026-01-18 14:30:00" 
            // to datetime-local format "2026-01-18T14:30"
            const date = new Date(dateTimeString);
            
            const year = date.getFullYear();
            const month = (date.getMonth() + 1).toString().padStart(2, '0');
            const day = date.getDate().toString().padStart(2, '0');
            const hours = date.getHours().toString().padStart(2, '0');
            const minutes = date.getMinutes().toString().padStart(2, '0');
            
            return `${year}-${month}-${day}T${hours}:${minutes}`;
        }
    }));
});
