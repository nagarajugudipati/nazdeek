document.getElementById("quoteForm").addEventListener("submit", function(e) {
    const name = document.getElementById("name").value.trim();
    const mobile = document.getElementById("mobile").value.trim();
    const pincode = document.getElementById("pincode").value.trim();

    if (!/^[A-Za-z ]{2,50}$/.test(name)) {
        alert("Please enter a valid name.");
        e.preventDefault();
        return;
    }

    if (!/^[6-9][0-9]{9}$/.test(mobile)) {
        alert("Please enter a valid 10-digit mobile number.");
        e.preventDefault();
        return;
    }

    if (!/^[0-9]{6}$/.test(pincode)) {
        alert("Please enter a valid 6-digit PIN Code.");
        e.preventDefault();
        return;
    }

    alert("Form submitted successfully!");
});
