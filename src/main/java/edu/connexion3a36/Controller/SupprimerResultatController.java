package edu.connexion3a36.Controller;

import edu.connexion3a36.entities.Resultat;
import edu.connexion3a36.services.ResultatService;
import javafx.collections.FXCollections;
import javafx.fxml.FXML;
import javafx.scene.control.TableColumn;
import javafx.scene.control.TableView;
import javafx.scene.control.cell.PropertyValueFactory;

import java.sql.SQLException;
import java.time.LocalDateTime;

public class SupprimerResultatController {

    @FXML
    private TableView<Resultat> resultatTable;

    @FXML
    private TableColumn<Resultat, Integer> idResultatCol;

    @FXML
    private TableColumn<Resultat, Double> scoreCol;

    @FXML
    private TableColumn<Resultat, LocalDateTime> createdAtCol;

    private final ResultatService resultatService = new ResultatService();

    @FXML
    public void initialize() {
        idResultatCol.setCellValueFactory(new PropertyValueFactory<>("idResultat"));
        scoreCol.setCellValueFactory(new PropertyValueFactory<>("score"));
        createdAtCol.setCellValueFactory(new PropertyValueFactory<>("createdAt"));
        chargerResultats();
    }

    @FXML
    public void supprimerResultat() {
        Resultat selectedResultat = resultatTable.getSelectionModel().getSelectedItem();
        if (selectedResultat == null) {
            ControllerUtils.showWarning("Selectionnez un resultat a supprimer.");
            return;
        }

        try {
            resultatService.supprimerResultat(selectedResultat.getIdResultat());
            ControllerUtils.showInfo("Resultat supprime avec succes.");
            chargerResultats();
        } catch (SQLException e) {
            ControllerUtils.showError("Erreur lors de la suppression du resultat : " + e.getMessage());
        }
    }

    private void chargerResultats() {
        try {
            resultatTable.setItems(FXCollections.observableArrayList(resultatService.afficherResultat()));
        } catch (SQLException e) {
            ControllerUtils.showError("Erreur lors du chargement des resultats : " + e.getMessage());
        }
    }
}