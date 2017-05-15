# Boise State Gravity Forms to Google Calendar

OVERVIEW:
Exports Gravity Form entries into a Google Calendar.

PROGRAM DESIGN AND IMPORTANT CONCEPTS:
First, the program requires the Google Calendar API and authenticates.
The authentication credentials come from a file that I generated under a Google API Service Account here:
https://console.developers.google.com/iam-admin/serviceaccounts/

After authentication is successful, the values from the Gravity Form submission are taken and put into a
Google Calendar event. Depending on what "Promotion" type was chosen, the event will go to a certain
Google Calendar.

TESTING:
Todo.
