<style>
    /* Footer styles for the copyright text */
    .simple-footer {
        background-color: #0d0d0d; /* Match the body background */
        color: #fff;
        padding: 20px 0;
        text-align: center;
        font-family: 'Poppins', sans-serif;
        font-size: 14px;
        position: relative; /* Allows the orange line to be positioned relative to the footer */
        width: 100%; /* Make footer span full width */
        margin: 0;   /* Remove any auto-centering */
    }

    /* Orange line above the footer text */
    .simple-footer::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 2px;
        background-color: #FF4500; /* The requested orange color */
    }
</style>

<div class="simple-footer">
    &copy; 2025 MealMate. All rights reserved.
</div>