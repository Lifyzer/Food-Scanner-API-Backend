#!/bin/bash

function save-project-to-repo() {
    git remote rm origin
    git remote add origin $1
    git push
}

save-project-to-repo git@bitbucket.org:pH_7/lifyzer-api-backend.git
save-project-to-repo git@gitlab.com:pH-7/lifyzer-api-backend.git
save-project-to-repo git@github.com:Lifyzer/Food-Scanner-API-Backend.git
